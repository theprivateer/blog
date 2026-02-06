<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show(Category $category)
    {
        $posts = Post::with('category')
                    ->published()
                    ->where('category_id', $category->id)
                    ->simplePaginate();

        return view('categories.show', [
            'category' => $category,
            'metadata' => $category->metadata,
            'posts' => $posts,
        ]);
    }
}
