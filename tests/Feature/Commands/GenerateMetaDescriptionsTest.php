<?php

namespace Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Filament\Blocks\PageBuilder\HeaderBlock;
use Privateer\Basecms\Filament\Blocks\PageBuilder\MarkdownBlock;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
use Privateer\Basecms\Services\GenerateMetaDescriptionAgent;
use Tests\TestCase;

class GenerateMetaDescriptionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);
        config()->set('basecms.ai.generate_meta_descriptions.enabled', true);
        config()->set('basecms.pages.builder.blocks', [MarkdownBlock::class, HeaderBlock::class]);
    }

    public function test_command_warns_and_exits_when_feature_is_disabled(): void
    {
        config()->set('basecms.ai.generate_meta_descriptions.enabled', false);

        $this->artisan('basecms:generate-meta-descriptions', ['model' => 'post'])
            ->expectsOutput('AI meta description generation is disabled. Set basecms.ai.generate_meta_descriptions.enabled to true to run this command.')
            ->assertSuccessful();
    }

    public function test_command_rejects_unsupported_models(): void
    {
        $this->artisan('basecms:generate-meta-descriptions', ['model' => 'note'])
            ->expectsOutput('Unsupported model [note]. Supported models are: post, page.')
            ->assertExitCode(1);
    }

    public function test_command_only_processes_records_with_missing_descriptions_by_default(): void
    {
        $postMissingMetadata = Post::factory()->create([
            'title' => 'Needs metadata',
            'body' => 'Needs a description.',
        ]);

        $postBlankDescription = Post::factory()->create([
            'title' => 'Blank description',
            'body' => 'Also needs a description.',
        ]);
        $postBlankDescription->metadata()->create([
            'title' => 'Keep this title',
            'description' => '',
        ]);

        $postWithDescription = Post::factory()->create([
            'title' => 'Existing description',
            'body' => 'Already described.',
        ]);
        $postWithDescription->metadata()->create([
            'title' => 'Existing SEO title',
            'description' => 'Leave this description alone.',
        ]);

        GenerateMetaDescriptionAgent::fake([
            ['description' => 'Search-ready summary for the first post that clearly explains the content without repeating the title in the snippet text.'],
            ['description' => 'Useful snippet for the second post that fills the missing SEO description while keeping the existing metadata title intact.'],
        ])->preventStrayPrompts();

        $this->artisan('basecms:generate-meta-descriptions', ['model' => 'post'])
            ->expectsOutputToContain('Generating meta descriptions for 2 post records...')
            ->expectsOutput('Processed 2 post records.')
            ->expectsOutput('Updated 2 descriptions.')
            ->expectsOutput('Skipped 0 records.')
            ->assertSuccessful();

        $this->assertSame(
            'Search-ready summary for the first post that clearly explains the content without repeating the title in the snippet text.',
            $postMissingMetadata->fresh()->metadata?->description
        );
        $this->assertSame(
            'Useful snippet for the second post that fills the missing SEO description while keeping the existing metadata title intact.',
            $postBlankDescription->fresh()->metadata?->description
        );
        $this->assertSame('Keep this title', $postBlankDescription->fresh()->metadata?->title);
        $this->assertSame('Leave this description alone.', $postWithDescription->fresh()->metadata?->description);
    }

    public function test_force_option_processes_records_that_already_have_descriptions(): void
    {
        $post = Post::factory()->create([
            'title' => 'Rewrite me',
            'body' => 'This description should be replaced.',
        ]);
        $post->metadata()->create([
            'title' => 'Keep this title',
            'description' => 'Old description.',
        ]);

        GenerateMetaDescriptionAgent::fake([
            ['description' => 'Freshly generated search snippet for this post that replaces the old description but leaves the SEO title untouched.'],
        ])->preventStrayPrompts();

        $this->artisan('basecms:generate-meta-descriptions', ['model' => 'post', '--force' => true])
            ->expectsOutputToContain('Generating meta descriptions for 1 post records with --force...')
            ->expectsOutput('Processed 1 post records.')
            ->expectsOutput('Updated 1 descriptions.')
            ->assertSuccessful();

        $this->assertSame(
            'Freshly generated search snippet for this post that replaces the old description but leaves the SEO title untouched.',
            $post->fresh()->metadata?->description
        );
        $this->assertSame('Keep this title', $post->fresh()->metadata?->title);
    }

    public function test_command_uses_builder_backed_page_content(): void
    {
        $page = Page::factory()->create([
            'title' => 'Builder Page',
            'use_builder' => true,
            'body' => 'Unused body',
            'blocks' => [
                [
                    'type' => 'header',
                    'data' => [
                        'heading' => 'Builder heading',
                        'content' => 'Builder intro copy',
                    ],
                ],
                [
                    'type' => 'markdown',
                    'data' => [
                        'content' => 'Builder paragraph copy.',
                    ],
                ],
            ],
        ]);

        GenerateMetaDescriptionAgent::fake([
            ['description' => 'Concise page summary based on builder-rendered content that is ready for search metadata and avoids repeating the page title.'],
        ])->preventStrayPrompts();

        $this->artisan('basecms:generate-meta-descriptions', ['model' => 'page'])
            ->assertSuccessful();

        GenerateMetaDescriptionAgent::assertPrompted(function ($prompt): bool {
            return str_contains($prompt->prompt, 'Builder heading')
                && str_contains($prompt->prompt, 'Builder paragraph copy.')
                && ! str_contains($prompt->prompt, 'Unused body');
        });

        $this->assertSame(
            'Concise page summary based on builder-rendered content that is ready for search metadata and avoids repeating the page title.',
            $page->fresh()->metadata?->description
        );
    }

    public function test_command_continues_after_record_failures_and_reports_counts(): void
    {
        $failing = Post::factory()->create([
            'title' => 'Failure case',
            'body' => 'This one will fail.',
        ]);

        $working = Post::factory()->create([
            'title' => 'Working case',
            'body' => 'This one will succeed.',
        ]);

        GenerateMetaDescriptionAgent::fake(function (string $prompt): array {
            if (str_contains($prompt, 'Failure case')) {
                throw new \RuntimeException('Provider unavailable');
            }

            return [
                'description' => 'Generated snippet for the working record after another record failed during the same bulk run.',
            ];
        })->preventStrayPrompts();

        $this->artisan('basecms:generate-meta-descriptions', ['model' => 'post'])
            ->expectsOutputToContain("Failed post [{$failing->id}] [Failure case]: Provider unavailable")
            ->expectsOutput('Processed 2 post records.')
            ->expectsOutput('Updated 1 descriptions.')
            ->expectsOutput('Skipped 0 records.')
            ->expectsOutput('Failed 1 records.')
            ->assertSuccessful();

        $this->assertNull($failing->fresh()->metadata);
        $this->assertSame(
            'Generated snippet for the working record after another record failed during the same bulk run.',
            $working->fresh()->metadata?->description
        );
    }

    public function test_command_reports_empty_datasets_cleanly(): void
    {
        $post = Post::factory()->create([
            'title' => 'Already done',
            'body' => 'No generation needed.',
        ]);
        $post->metadata()->create([
            'description' => 'Existing description.',
        ]);

        $this->artisan('basecms:generate-meta-descriptions', ['model' => 'post'])
            ->expectsOutput('No post records found to process.')
            ->assertSuccessful();
    }
}
