<?php

namespace Tests\Feature\Feature;

use Illuminate\Http\Request;
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
}
