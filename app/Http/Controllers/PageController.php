<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Models\Page;
use App\Models\Post;

class PageController extends Controller
{
    /**
     * Display homepage.
     */
    public function index()
    {
        // Homepage
        $page = Page::where('is_homepage', true)
                    ->firstOrFail();

        $posts = Post::published()->take(5)->get();

        return view('pages.index', [
            'page' => $page,
            'posts' => $posts,
        ]);
    }

    /**
     * Display the specified page.
     */
    public function show(Page $page)
    {
        return view($page->template ?: 'pages.show', [
            'page' => $page,
        ]);
    }
}
