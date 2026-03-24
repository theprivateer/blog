<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Models\Category;
use Privateer\Basecms\Models\Metadata;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
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
