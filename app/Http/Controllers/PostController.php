<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page = Page::where('slug', 'blog')
                    ->firstOrFail();

        $posts = Post::published()->simplePaginate();

        return view('posts.index', [
            'page' => $page,
            'metadata' => $page->metadata,
            'posts' => $posts,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return view('posts.show', [
            'post' => $post,
            'metadata' => $post->metadata,
        ]);
    }
}
