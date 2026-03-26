<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
use Privateer\Basecms\Http\Controllers\PostController as BasePostController;

class PostController extends BasePostController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $page = Page::where('slug', 'blog')
            ->firstOrFail();

        $posts = Post::with('category')->published()->simplePaginate();

        return view((string) config('basecms.views.posts.index', 'posts.index'), [
            'page' => $page,
            'metadata' => $page->metadata,
            'posts' => $posts,
        ]);
    }
}
