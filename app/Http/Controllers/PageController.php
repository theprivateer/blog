<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Privateer\Basecms\Http\Controllers\PageController as BasePageController;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
use Privateer\Basecms\Services\SiteManager;

class PageController extends BasePageController
{
    /**
     * Display homepage.
     */
    public function index(): View
    {
        $site = app(SiteManager::class)->siteForRequest();

        $page = Page::query()
            ->forSite($site)
            ->where('is_homepage', true)
            ->firstOrFail();

        $posts = Post::query()
            ->forSite($site)
            ->with('category')
            ->published()
            ->take(5)
            ->get();

        return view((string) config('basecms.views.pages.index', 'pages.index'), [
            'page' => $page,
            'metadata' => $page->metadata,
            'posts' => $posts,
        ]);
    }
}
