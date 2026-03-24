<?php

namespace Privateer\Basecms\Http\Controllers;

use Illuminate\Contracts\View\View;
use Privateer\Basecms\Models\Category;
use Privateer\Basecms\Models\Post;

class CategoryController extends Controller
{
    public function show(Category $category): View
    {
        $posts = Post::published()
            ->where('category_id', $category->id)
            ->simplePaginate();

        return view((string) config('basecms.views.categories.show', 'categories.show'), [
            'category' => $category,
            'metadata' => $category->metadata,
            'posts' => $posts,
        ]);
    }
}
