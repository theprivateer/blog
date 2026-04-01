<?php

namespace Tests\Feature\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Filament\Blocks\PageBuilder\HeaderBlock;
use Privateer\Basecms\Filament\Blocks\PageBuilder\MarkdownBlock;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
use Privateer\Basecms\Services\GenerateMetaDescriptionAgent;
use Privateer\Basecms\Services\MetaDescriptionGenerationException;
use Privateer\Basecms\Services\MetaDescriptionGenerator;
use Tests\TestCase;

class MetaDescriptionGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private MetaDescriptionGenerator $service;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);

        $this->service = app(MetaDescriptionGenerator::class);
    }

    public function test_generate_uses_current_unsaved_post_form_content(): void
    {
        $post = Post::factory()->create([
            'title' => 'Saved title',
            'body' => 'Saved body',
        ]);

        GenerateMetaDescriptionAgent::fake([
            ['description' => 'Fresh summary of the updated post body without repeating the title, shaped as a clean and informative search snippet for readers.'],
        ])->preventStrayPrompts();

        $description = $this->service->generate($post, [
            'title' => 'Draft post title',
            'body' => '# Heading'."\n\n".'Draft paragraph with **formatting** and extra detail.',
        ]);

        $this->assertSame('Fresh summary of the updated post body without repeating the title, shaped as a clean and informative search snippet for readers.', $description);

        GenerateMetaDescriptionAgent::assertPrompted(function ($prompt): bool {
            return str_contains($prompt->prompt, 'Draft post title')
                && str_contains($prompt->prompt, 'Draft paragraph with formatting and extra detail.')
                && ! str_contains($prompt->prompt, '<p>')
                && ! str_contains($prompt->prompt, 'Saved body');
        });
    }

    public function test_generate_uses_rendered_builder_content_for_pages(): void
    {
        config()->set('basecms.pages.builder.blocks', [MarkdownBlock::class, HeaderBlock::class]);

        $page = Page::factory()->create([
            'title' => 'Saved page title',
            'body' => 'Saved page body',
        ]);

        GenerateMetaDescriptionAgent::fake([
            ['description' => 'Helpful overview of the builder-based page content that stays concise, readable, and useful in search results without echoing the page title.'],
        ])->preventStrayPrompts();

        $description = $this->service->generate($page, [
            'title' => 'Builder page title',
            'use_builder' => true,
            'blocks' => [
                [
                    'type' => 'header',
                    'data' => [
                        'content' => 'Builder heading',
                        'level' => '2',
                    ],
                ],
                [
                    'type' => 'markdown',
                    'data' => [
                        'content' => 'Builder paragraph content.',
                    ],
                ],
            ],
        ]);

        $this->assertSame('Helpful overview of the builder-based page content that stays concise, readable, and useful in search results without echoing the page title.', $description);

        GenerateMetaDescriptionAgent::assertPrompted(function ($prompt): bool {
            return str_contains($prompt->prompt, 'Builder page title')
                && str_contains($prompt->prompt, 'Builder heading')
                && str_contains($prompt->prompt, 'Builder paragraph content.')
                && ! str_contains($prompt->prompt, '<h2>')
                && ! str_contains($prompt->prompt, '<p>');
        });
    }

    public function test_generate_throws_when_title_or_content_is_blank(): void
    {
        $page = Page::factory()->create();

        GenerateMetaDescriptionAgent::fake()->preventStrayPrompts();

        try {
            $this->service->generate($page, [
                'title' => '   ',
                'body' => '   ',
            ]);

            $this->fail('Expected a meta description generation exception to be thrown.');
        } catch (MetaDescriptionGenerationException $exception) {
            $this->assertSame('A title and body are required to generate a meta description.', $exception->getMessage());
        }

        GenerateMetaDescriptionAgent::assertNeverPrompted();
    }
}
