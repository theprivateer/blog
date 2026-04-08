<?php

namespace Privateer\Basecms\Http\Controllers;

use Illuminate\Contracts\View\View;
use Privateer\Basecms\Models\Category;
use Privateer\Basecms\Models\Post;
use Privateer\Basecms\Services\SiteManager;

class CategoryController extends Controller
{
    public function show(string $category): View
    {
        $site = app(SiteManager::class)->siteForRequest();

        $category = Category::query()
            ->forSite($site)
            ->where('slug', $category)
            ->firstOrFail();

        $posts = Post::published()
            ->forSite($site)
            ->where('category_id', $category->id)
            ->simplePaginate();

        return view((string) config('basecms.views.categories.show', 'categories.show'), [
            'category' => $category,
            'metadata' => $category->metadata,
            'posts' => $posts,
        ]);
    }
}
