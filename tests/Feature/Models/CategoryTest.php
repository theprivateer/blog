<?php

namespace Tests\Feature\Models;

use App\Events\PostDeleted;
use App\Events\PostSaved;
use App\Models\Category;
use App\Models\Metadata;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);
    }

    public function test_slug_generated_from_title(): void
    {
        $category = Category::factory()->create(['title' => 'Laravel Tips']);

        $this->assertEquals('laravel-tips', $category->slug);
    }

    public function test_slug_not_updated_on_title_change(): void
    {
        $category = Category::factory()->create(['title' => 'Original']);
        $originalSlug = $category->slug;

        $category->update(['title' => 'Changed']);

        $this->assertEquals($originalSlug, $category->fresh()->slug);
    }

    public function test_route_key_name_is_slug(): void
    {
        $this->assertEquals('slug', (new Category)->getRouteKeyName());
    }

    public function test_morph_one_metadata(): void
    {
        $category = Category::factory()->create();
        $metadata = Metadata::factory()->create([
            'parent_type' => Category::class,
            'parent_id' => $category->id,
        ]);

        $this->assertInstanceOf(Metadata::class, $category->metadata);
        $this->assertEquals($metadata->id, $category->metadata->id);
    }

    public function test_get_disk_name_returns_categories(): void
    {
        $this->assertEquals('categories', (new Category)->getDiskName());
    }

    public function test_get_flat_file_filename_is_slug_dot_md(): void
    {
        $category = Category::factory()->create(['title' => 'Laravel']);

        $this->assertEquals('laravel.md', $category->getFlatFileFilename());
    }

    public function test_get_frontmatter_columns_includes_id(): void
    {
        $expected = ['id', 'title', 'created_at', 'updated_at'];

        $this->assertEquals($expected, (new Category)->getFrontmatterColumns());
    }

    public function test_dispatches_post_saved_event(): void
    {
        Category::factory()->create();

        Event::assertDispatched(PostSaved::class);
    }

    public function test_dispatches_post_deleted_event(): void
    {
        $category = Category::factory()->create();
        $category->delete();

        Event::assertDispatched(PostDeleted::class);
    }
}
