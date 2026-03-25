<?php

namespace Tests\Feature\Feature\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Http\Controllers\PageController;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
use Tests\TestCase;

class PackagePageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);
    }

    public function test_package_page_controller_index_does_not_provide_posts_by_default(): void
    {
        $page = Page::factory()->homepage()->create();
        Post::factory()->published()->count(3)->create();

        $response = $this->app->make(PageController::class)->index();

        $this->assertSame('pages.index', $response->name());
        $this->assertSame($page->id, $response->getData()['page']->id);
        $this->assertArrayNotHasKey('posts', $response->getData());
    }
}
