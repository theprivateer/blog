<?php

namespace Privateer\Basecms\Http\Controllers;

use Illuminate\Contracts\View\View;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;

class PostController extends Controller
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

    /**
     * Display the specified resource.
     */
    public function show(Post $post): View
    {
        $post->load('category');

        return view((string) config('basecms.views.posts.show', 'posts.show'), [
            'post' => $post,
            'metadata' => $post->metadata,
        ]);
    }
}
