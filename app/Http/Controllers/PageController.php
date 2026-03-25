<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
use Privateer\Basecms\Http\Controllers\PageController as BasePageController;

class PageController extends BasePageController
{
    /**
     * Display homepage.
     */
    public function index(): View
    {
        $page = Page::query()
            ->where('is_homepage', true)
            ->firstOrFail();

        $posts = Post::query()
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
