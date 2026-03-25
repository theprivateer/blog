<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController as AppPageController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
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

    public function test_homepage_route_uses_app_page_controller_override(): void
    {
        $route = app('router')->getRoutes()->getByName('home');

        $this->assertSame(AppPageController::class.'@index', $route->getActionName());
    }

    public function test_homepage_displays_latest_five_posts(): void
    {
        Page::factory()->homepage()->create();

        Post::factory()->published()->count(7)->create();

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

    public function test_page_show_does_not_render_markdown_body_for_builder_backed_pages(): void
    {
        $page = Page::factory()->create([
            'title' => 'Builder Page',
            'body' => 'This should not render',
            'use_builder' => true,
            'blocks' => [
                [
                    'type' => 'markdown',
                    'data' => ['content' => 'Builder block content'],
                ],
            ],
        ]);

        $response = $this->get('/'.$page->slug);

        $response->assertStatus(200);
        $response->assertDontSee('This should not render');
    }

    public function test_page_show_renders_markdown_block_content_for_builder_backed_pages(): void
    {
        $page = Page::factory()->create([
            'title' => 'Builder Page',
            'use_builder' => true,
            'blocks' => [
                [
                    'type' => 'markdown',
                    'data' => ['content' => 'Builder **block** content'],
                ],
            ],
        ]);

        $response = $this->get('/'.$page->slug);

        $response->assertStatus(200);
        $response->assertSee('Builder');
        $response->assertSee('block', false);
    }

    public function test_page_show_renders_header_block_content_for_builder_backed_pages(): void
    {
        $page = Page::factory()->create([
            'title' => 'Builder Page',
            'use_builder' => true,
            'blocks' => [
                [
                    'type' => 'header',
                    'data' => [
                        'heading' => 'Welcome',
                        'content' => 'Intro **copy**',
                    ],
                ],
            ],
        ]);

        $response = $this->get('/'.$page->slug);

        $response->assertStatus(200);
        $response->assertSee('Welcome');
        $response->assertSee('copy', false);
    }

    public function test_homepage_renders_builder_blocks(): void
    {
        Page::factory()->homepage()->create([
            'use_builder' => true,
            'blocks' => [
                [
                    'type' => 'header',
                    'data' => [
                        'heading' => 'Homepage heading',
                        'content' => 'Homepage **intro**',
                    ],
                ],
            ],
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Homepage heading');
        $response->assertSee('intro', false);
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
