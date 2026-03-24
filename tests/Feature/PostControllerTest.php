<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Models\Category;
use Privateer\Basecms\Models\Metadata;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);

        Page::factory()->create(['title' => 'Blog', 'slug' => 'blog']);
    }

    public function test_blog_index_returns_ok(): void
    {
        $response = $this->get('/blog');

        $response->assertStatus(200);
        $response->assertViewIs('posts.index');
    }

    public function test_blog_index_displays_published_posts_only(): void
    {
        $published = Post::factory()->published()->create(['title' => 'Published Post']);
        $unpublished = Post::factory()->unpublished()->create(['title' => 'Draft Post']);
        $future = Post::factory()->future()->create(['title' => 'Future Post']);

        $response = $this->get('/blog');

        $response->assertSee('Published Post');
        $response->assertDontSee('Draft Post');
        $response->assertDontSee('Future Post');
    }

    public function test_blog_index_eager_loads_categories(): void
    {
        $category = Category::factory()->create(['title' => 'Laravel']);
        Post::factory()->published()->create(['category_id' => $category->id]);

        $response = $this->get('/blog');

        $response->assertStatus(200);
        $response->assertSee('Laravel');
    }

    public function test_blog_index_paginates_posts(): void
    {
        Post::factory()->published()->count(20)->create();

        $response = $this->get('/blog');
        $response->assertStatus(200);

        $response = $this->get('/blog?page=2');
        $response->assertStatus(200);
    }

    public function test_blog_show_returns_ok_for_published_post(): void
    {
        $post = Post::factory()->published()->create(['title' => 'My Post']);

        $response = $this->get('/blog/'.$post->slug);

        $response->assertStatus(200);
        $response->assertViewIs('posts.show');
        $response->assertSee('My Post');
    }

    public function test_blog_show_loads_metadata(): void
    {
        $post = Post::factory()->published()->create();
        Metadata::factory()->create([
            'parent_type' => Post::class,
            'parent_id' => $post->id,
            'title' => 'SEO Title',
        ]);

        $response = $this->get('/blog/'.$post->slug);

        $response->assertStatus(200);
        $response->assertViewHas('metadata');
    }

    public function test_blog_show_returns_404_for_nonexistent_slug(): void
    {
        $response = $this->get('/blog/nonexistent');

        $response->assertStatus(404);
    }

    public function test_legacy_posts_redirects_to_blog(): void
    {
        $response = $this->get('/posts');

        $response->assertRedirect(route('posts.index'));
    }

    public function test_legacy_post_slug_redirects_to_blog_slug(): void
    {
        $post = Post::factory()->published()->create();

        $response = $this->get('/posts/'.$post->slug);

        $response->assertRedirect(route('posts.show', $post));
    }
}
