<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PostsShowController extends Controller
{
    public function __invoke(Post $post)
    {
        return view('posts.show', [
            'post' => $post,
        ]);
    }
}
