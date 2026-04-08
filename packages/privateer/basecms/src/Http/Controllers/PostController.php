<?php

namespace Privateer\Basecms\Http\Controllers;

use Illuminate\Contracts\View\View;
use Privateer\Basecms\Models\Metadata;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
use Privateer\Basecms\Services\SiteManager;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $site = app(SiteManager::class)->siteForRequest();

        $posts = Post::query()
            ->forSite($site)
            ->with('category')
            ->published()
            ->simplePaginate();

        $listingPage = Page::query()
            ->forSite($site)
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
    public function show(string $post): View
    {
        $site = app(SiteManager::class)->siteForRequest();

        $post = Post::query()
            ->forSite($site)
            ->published()
            ->where('slug', $post)
            ->firstOrFail();

        $post->load('category');

        return view((string) config('basecms.views.posts.show', 'posts.show'), [
            'post' => $post,
            'metadata' => $post->metadata,
        ]);
    }
}
