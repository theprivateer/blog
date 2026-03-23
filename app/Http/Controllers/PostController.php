<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;
use Illuminate\Contracts\View\View;

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

        return view('posts.index', [
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

        return view('posts.show', [
            'post' => $post,
            'metadata' => $post->metadata,
        ]);
    }
}
