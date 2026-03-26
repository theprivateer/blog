<?php

namespace Privateer\Basecms\Http\Controllers;

use Illuminate\Contracts\View\View;
use Privateer\Basecms\Models\Metadata;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $posts = Post::with('category')->published()->simplePaginate();
        $listingPage = Page::query()
            ->where('slug', 'blog')
            ->first();

        $metadata = $listingPage?->metadata ?? Metadata::make([
            'title' => 'Blog',
        ]);

        return view((string) config('basecms.views.posts.index', 'posts.index'), [
            'listingPage' => $listingPage,
            'posts' => $posts,
            'metadata' => $metadata,
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
