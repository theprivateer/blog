<?php

namespace Privateer\Basecms\Http\Controllers;

use Illuminate\Contracts\View\View;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;

class PageController extends Controller
{
    /**
     * Display homepage.
     */
    public function index(): View
    {
        $page = Page::where('is_homepage', true)
            ->firstOrFail();

        $posts = Post::with('category')->published()->take(5)->get();

        return view((string) config('basecms.views.pages.index', 'pages.index'), [
            'page' => $page,
            'metadata' => $page->metadata,
            'posts' => $posts,
        ]);
    }

    /**
     * Display the specified page.
     */
    public function show(Page $page): View
    {
        if ($page->draft) {
            abort(404);
        }

        return view($page->template ?: config('basecms.views.pages.show', 'pages.show'), [
            'page' => $page,
            'metadata' => $page->metadata,
        ]);
    }
}
