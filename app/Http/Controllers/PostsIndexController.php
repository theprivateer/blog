<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Sheets\Facades\Sheets;

class PostsIndexController extends Controller
{
    public function __invoke()
    {
        $posts = Sheets::collection('posts')
                    ->all()
                    ->sortByDesc('date');

        return view('posts.index', [
           'posts' => $posts,
        ]);
    }
}
