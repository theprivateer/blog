<?php

namespace Tests\Feature\Feature;

use App\Http\Controllers\PageController as AppPageController;
use Illuminate\Http\Request;
use Privateer\Basecms\Http\Controllers\CategoryController;
use Privateer\Basecms\Http\Controllers\PostController;
use Tests\TestCase;

class BasecmsRoutingTest extends TestCase
{
    public function test_notes_index_route_wins_before_package_page_wildcard_route(): void
    {
        $route = app('router')->getRoutes()->match(Request::create('/notes', 'GET'));

        $this->assertSame('notes.index', $route->getName());
    }

    public function test_package_page_wildcard_still_handles_other_single_segment_pages(): void
    {
        $route = app('router')->getRoutes()->match(Request::create('/about', 'GET'));

        $this->assertSame('pages.show', $route->getName());
    }

    public function test_routes_use_configured_controller_classes(): void
    {
        $routes = app('router')->getRoutes();

        $this->assertSame(AppPageController::class.'@index', $routes->getByName('home')->getActionName());
        $this->assertSame(PostController::class.'@index', $routes->getByName('posts.index')->getActionName());
        $this->assertSame(PostController::class.'@show', $routes->getByName('posts.show')->getActionName());
        $this->assertSame(CategoryController::class.'@show', $routes->getByName('categories.show')->getActionName());
        $this->assertSame(AppPageController::class.'@show', $routes->getByName('pages.show')->getActionName());
    }
}
