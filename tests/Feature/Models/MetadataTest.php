<?php

namespace Tests\Feature\Models;

use App\Events\PostDeleted;
use App\Events\PostSaved;
use App\Models\Category;
use App\Models\Metadata;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MetadataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);
    }

    public function test_belongs_to_parent_post(): void
    {
        $post = Post::factory()->create();
        $metadata = Metadata::factory()->create([
            'parent_type' => Post::class,
            'parent_id' => $post->id,
        ]);

        $this->assertInstanceOf(Post::class, $metadata->parent);
    }

    public function test_belongs_to_parent_page(): void
    {
        $page = Page::factory()->create();
        $metadata = Metadata::factory()->create([
            'parent_type' => Page::class,
            'parent_id' => $page->id,
        ]);

        $this->assertInstanceOf(Page::class, $metadata->parent);
    }

    public function test_belongs_to_parent_category(): void
    {
        $category = Category::factory()->create();
        $metadata = Metadata::factory()->create([
            'parent_type' => Category::class,
            'parent_id' => $category->id,
        ]);

        $this->assertInstanceOf(Category::class, $metadata->parent);
    }

    public function test_to_array_returns_only_title_and_description(): void
    {
        $post = Post::factory()->create();
        $metadata = Metadata::factory()->create([
            'parent_type' => Post::class,
            'parent_id' => $post->id,
            'title' => 'SEO Title',
            'description' => 'SEO Description',
        ]);

        $array = $metadata->toArray();

        $this->assertEquals(['title' => 'SEO Title', 'description' => 'SEO Description'], $array);
        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayNotHasKey('parent_type', $array);
        $this->assertArrayNotHasKey('parent_id', $array);
    }
}
