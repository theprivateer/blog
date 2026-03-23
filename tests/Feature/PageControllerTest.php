<?php

namespace Tests\Feature;

use App\Events\PostDeleted;
use App\Events\PostSaved;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);
    }

    public function test_homepage_returns_ok(): void
    {
        $page = Page::factory()->homepage()->create();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('pages.index');
        $response->assertViewHas('page', $page);
    }

    public function test_homepage_displays_latest_five_posts(): void
    {
        Page::factory()->homepage()->create();

        $posts = Post::factory()->published()->count(7)->create();
        $sortedPosts = $posts->sortByDesc('published_at');

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('posts', function ($viewPosts) {
            return $viewPosts->count() === 5;
        });
    }

    public function test_homepage_returns_404_when_no_homepage_exists(): void
    {
        $response = $this->get('/');

        $response->assertStatus(404);
    }

    public function test_page_show_returns_ok_for_non_draft_page(): void
    {
        $page = Page::factory()->create(['title' => 'About', 'draft' => false]);

        $response = $this->get('/'.$page->slug);

        $response->assertStatus(200);
        $response->assertSee('About');
    }

    public function test_page_show_returns_404_for_draft_page(): void
    {
        $page = Page::factory()->draft()->create();

        $response = $this->get('/'.$page->slug);

        $response->assertStatus(404);
    }

    public function test_page_show_uses_custom_template_when_set(): void
    {
        $page = Page::factory()->create(['template' => 'pages.now']);

        $response = $this->get('/'.$page->slug);

        $response->assertStatus(200);
        $response->assertViewIs('pages.now');
    }

    public function test_page_show_uses_default_template_when_no_custom_template(): void
    {
        $page = Page::factory()->create(['template' => null]);

        $response = $this->get('/'.$page->slug);

        $response->assertStatus(200);
        $response->assertViewIs('pages.show');
    }

    public function test_page_show_returns_404_for_nonexistent_slug(): void
    {
        $response = $this->get('/nonexistent');

        $response->assertStatus(404);
    }
}
