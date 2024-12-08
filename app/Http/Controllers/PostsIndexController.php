<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Spatie\Sheets\Facades\Sheets;

class PostsIndexController extends Controller
{
    public function __invoke(): View
    {
        $posts = Sheets::collection('posts')
                    ->all()
                    ->sortByDesc('date');

        return view('posts.index', [
           'posts' => $posts,
        ]);
    }
}
