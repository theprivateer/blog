<?php

namespace Tests\Feature\Models;

use App\Events\PostDeleted;
use App\Events\PostSaved;
use App\Models\Metadata;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);
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
        $expected = ['title', 'template', 'draft', 'created_at', 'updated_at'];

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
}
