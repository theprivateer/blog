<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Metadata;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Spatie\Feed\FeedItem;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);
    }

    public function test_published_scope_filters_unpublished_posts(): void
    {
        Post::factory()->unpublished()->create();

        $this->assertCount(0, Post::published()->get());
    }

    public function test_published_scope_filters_future_posts(): void
    {
        Post::factory()->future()->create();

        $this->assertCount(0, Post::published()->get());
    }

    public function test_published_scope_includes_published_posts(): void
    {
        Post::factory()->published()->create();

        $this->assertCount(1, Post::published()->get());
    }

    public function test_published_scope_orders_by_published_at_desc(): void
    {
        $old = Post::factory()->create(['published_at' => now()->subDays(10)]);
        $new = Post::factory()->create(['published_at' => now()->subDay()]);
        $mid = Post::factory()->create(['published_at' => now()->subDays(5)]);

        $posts = Post::published()->get();

        $this->assertEquals($new->id, $posts->first()->id);
        $this->assertEquals($old->id, $posts->last()->id);
    }

    public function test_belongs_to_category(): void
    {
        $category = Category::factory()->create();
        $post = Post::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $post->category);
        $this->assertEquals($category->id, $post->category->id);
    }

    public function test_morph_one_metadata(): void
    {
        $post = Post::factory()->create();
        $metadata = Metadata::factory()->create([
            'parent_type' => Post::class,
            'parent_id' => $post->id,
        ]);

        $this->assertInstanceOf(Metadata::class, $post->metadata);
        $this->assertEquals($metadata->id, $post->metadata->id);
    }

    public function test_slug_generated_from_title(): void
    {
        $post = Post::factory()->create(['title' => 'My First Post']);

        $this->assertEquals('my-first-post', $post->slug);
    }

    public function test_slug_not_updated_on_title_change(): void
    {
        $post = Post::factory()->create(['title' => 'Original Title']);
        $originalSlug = $post->slug;

        $post->update(['title' => 'New Title']);

        $this->assertEquals($originalSlug, $post->fresh()->slug);
    }

    public function test_route_key_name_is_slug(): void
    {
        $post = new Post;

        $this->assertEquals('slug', $post->getRouteKeyName());
    }

    public function test_published_at_cast_to_datetime(): void
    {
        $post = Post::factory()->published()->create();

        $this->assertInstanceOf(Carbon::class, $post->published_at);
    }

    public function test_get_flat_file_filename_with_published_at(): void
    {
        $post = Post::factory()->create([
            'title' => 'Test Post',
            'published_at' => Carbon::parse('2025-01-15'),
        ]);

        $filename = $post->getFlatFileFilename();

        $this->assertStringContainsString('test-post.md', $filename);
        $this->assertStringContainsString('2025-01-15', $filename);
    }

    public function test_get_flat_file_filename_without_published_at(): void
    {
        $post = Post::factory()->unpublished()->create(['title' => 'Draft Post']);

        $this->assertEquals('draft-post.md', $post->getFlatFileFilename());
    }

    public function test_get_disk_name_returns_posts(): void
    {
        $this->assertEquals('posts', (new Post)->getDiskName());
    }

    public function test_get_frontmatter_columns_returns_expected_keys(): void
    {
        $expected = ['title', 'intro', 'published_at', 'category_id', 'created_at', 'updated_at'];

        $this->assertEquals($expected, (new Post)->getFrontmatterColumns());
    }

    public function test_dispatches_post_saved_event_on_save(): void
    {
        Post::factory()->create();

        Event::assertDispatched(PostSaved::class);
    }

    public function test_dispatches_post_deleted_event_on_delete(): void
    {
        $post = Post::factory()->create();
        $post->delete();

        Event::assertDispatched(PostDeleted::class);
    }

    public function test_get_feed_items_returns_max_20_published(): void
    {
        Post::factory()->published()->count(25)->create();

        $this->assertCount(20, Post::getFeedItems());
    }

    public function test_get_feed_items_excludes_unpublished(): void
    {
        Post::factory()->published()->create();
        Post::factory()->unpublished()->create();

        $this->assertCount(1, Post::getFeedItems());
    }

    public function test_to_feed_item_returns_feed_item(): void
    {
        $post = Post::factory()->published()->create(['title' => 'Feed Post']);

        $feedItem = $post->toFeedItem();

        $this->assertInstanceOf(FeedItem::class, $feedItem);
    }

    public function test_post_without_category_has_null_relationship(): void
    {
        $post = Post::factory()->create(['category_id' => null]);

        $this->assertNull($post->category);
    }
}
