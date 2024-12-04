<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Sheets\Facades\Sheets;

class PostsShowController extends Controller
{
    public function __invoke($slug)
    {
        $post = Sheets::collection('posts')
                    ->all()
                    ->where('slug', $slug)
                    ->first();

        abort_if(! $post, 404);

        return view('posts.show', [
            'post' => $post,
        ]);
    }
}
