<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Filament\Blocks\PageBuilder\HeaderBlock;
use Privateer\Basecms\Filament\Blocks\PageBuilder\MarkdownBlock;
use Privateer\Basecms\Models\Metadata;
use Privateer\Basecms\Models\Page;
use Tests\Fixtures\Filament\PageBuilder\MissingViewBlock;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);
        config()->set('basecms.pages.builder.blocks', [MarkdownBlock::class, HeaderBlock::class]);
    }

    public function test_slug_generated_from_title(): void
    {
        $page = Page::factory()->create(['title' => 'About Me']);

        $this->assertEquals('about-me', $page->slug);
    }

    public function test_slug_not_updated_on_title_change(): void
    {
        $page = Page::factory()->create(['title' => 'Original']);
        $originalSlug = $page->slug;

        $page->update(['title' => 'Changed']);

        $this->assertEquals($originalSlug, $page->fresh()->slug);
    }

    public function test_route_key_name_is_slug(): void
    {
        $this->assertEquals('slug', (new Page)->getRouteKeyName());
    }

    public function test_morph_one_metadata(): void
    {
        $page = Page::factory()->create();
        $metadata = Metadata::factory()->create([
            'parent_type' => Page::class,
            'parent_id' => $page->id,
        ]);

        $this->assertInstanceOf(Metadata::class, $page->metadata);
        $this->assertEquals($metadata->id, $page->metadata->id);
    }

    public function test_get_disk_name_returns_pages(): void
    {
        $this->assertEquals('pages', (new Page)->getDiskName());
    }

    public function test_get_flat_file_filename_is_slug_dot_md(): void
    {
        $page = Page::factory()->create(['title' => 'About']);

        $this->assertEquals('about.md', $page->getFlatFileFilename());
    }

    public function test_get_frontmatter_columns_returns_expected_keys(): void
    {
        $expected = ['title', 'use_builder', 'blocks', 'template', 'draft', 'created_at', 'updated_at'];

        $this->assertEquals($expected, (new Page)->getFrontmatterColumns());
    }

    public function test_dispatches_post_saved_event(): void
    {
        Page::factory()->create();

        Event::assertDispatched(PostSaved::class);
    }

    public function test_dispatches_post_deleted_event(): void
    {
        $page = Page::factory()->create();
        $page->delete();

        Event::assertDispatched(PostDeleted::class);
    }

    public function test_is_homepage_defaults_to_false(): void
    {
        $page = Page::factory()->create();

        $this->assertFalse((bool) $page->is_homepage);
    }

    public function test_draft_defaults_to_false(): void
    {
        $page = Page::factory()->create();

        $this->assertFalse((bool) $page->draft);
    }

    public function test_pages_table_includes_builder_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('pages', ['use_builder', 'blocks']));
    }

    public function test_use_builder_and_blocks_are_cast_correctly(): void
    {
        $page = Page::factory()->create([
            'use_builder' => true,
            'blocks' => [
                [
                    'type' => 'markdown',
                    'data' => ['content' => 'Hello from blocks'],
                ],
            ],
        ]);

        $page = $page->fresh();

        $this->assertTrue($page->use_builder);
        $this->assertSame([
            [
                'type' => 'markdown',
                'data' => ['content' => 'Hello from blocks'],
            ],
        ], $page->blocks);
    }

    public function test_render_returns_rendered_markdown_when_builder_is_disabled(): void
    {
        $page = Page::factory()->make([
            'body' => 'Hello **world**',
            'use_builder' => false,
        ]);

        $this->assertStringContainsString('<strong>world</strong>', $page->render());
    }

    public function test_render_returns_rendered_block_html_when_builder_is_enabled(): void
    {
        $page = Page::factory()->make([
            'use_builder' => true,
            'blocks' => [
                [
                    'type' => 'markdown',
                    'data' => ['content' => 'Hello **from** blocks'],
                ],
            ],
        ]);

        $this->assertStringContainsString('<strong>from</strong>', $page->render());
    }

    public function test_render_renders_multiple_builder_blocks(): void
    {
        $page = Page::factory()->make([
            'title' => 'Builder Page',
            'use_builder' => true,
            'blocks' => [
                [
                    'type' => 'header',
                    'data' => [
                        'heading' => 'Hello there',
                        'content' => 'Header **content**',
                    ],
                ],
                [
                    'type' => 'markdown',
                    'data' => [
                        'content' => 'Follow-up paragraph',
                    ],
                ],
            ],
        ]);

        $rendered = $page->render();

        $this->assertStringContainsString('Hello there', $rendered);
        $this->assertStringContainsString('<strong>content</strong>', $rendered);
        $this->assertStringContainsString('Follow-up paragraph', $rendered);
    }

    public function test_render_skips_unknown_builder_block_types(): void
    {
        $page = Page::factory()->make([
            'use_builder' => true,
            'blocks' => [
                [
                    'type' => 'unknown-block',
                    'data' => ['content' => 'Should be skipped'],
                ],
                [
                    'type' => 'markdown',
                    'data' => ['content' => 'Still rendered'],
                ],
            ],
        ]);

        $rendered = $page->render();

        $this->assertStringNotContainsString('Should be skipped', $rendered);
        $this->assertStringContainsString('Still rendered', $rendered);
    }

    public function test_render_skips_blocks_with_missing_views(): void
    {
        config()->set('basecms.pages.builder.blocks', [MissingViewBlock::class, MarkdownBlock::class]);

        $page = Page::factory()->make([
            'use_builder' => true,
            'blocks' => [
                [
                    'type' => 'missing-view',
                    'data' => ['content' => 'Should be skipped'],
                ],
                [
                    'type' => 'markdown',
                    'data' => ['content' => 'Still rendered'],
                ],
            ],
        ]);

        $rendered = $page->render();

        $this->assertStringNotContainsString('Should be skipped', $rendered);
        $this->assertStringContainsString('Still rendered', $rendered);
    }

    public function test_render_treats_non_array_block_data_as_empty_data(): void
    {
        $page = Page::factory()->make([
            'use_builder' => true,
            'blocks' => [
                [
                    'type' => 'header',
                    'data' => 'not-an-array',
                ],
                [
                    'type' => 'markdown',
                    'data' => ['content' => 'Still rendered'],
                ],
            ],
        ]);

        $rendered = $page->render();

        $this->assertStringNotContainsString('not-an-array', $rendered);
        $this->assertStringContainsString('Still rendered', $rendered);
    }

    public function test_package_block_views_are_registered(): void
    {
        $this->assertTrue(View::exists('basecms::blocks.page-builder.markdown'));
        $this->assertTrue(View::exists('basecms::blocks.page-builder.header'));
    }
}
