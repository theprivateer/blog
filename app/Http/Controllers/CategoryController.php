<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Contracts\View\View;

class CategoryController extends Controller
{
    public function show(Category $category): View
    {
        $posts = Post::published()
            ->where('category_id', $category->id)
            ->simplePaginate();

        return view('categories.show', [
            'category' => $category,
            'metadata' => $category->metadata,
            'posts' => $posts,
        ]);
    }
}
