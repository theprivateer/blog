<?php

namespace App\Http\Controllers;

use App\Models\Post;
use TOC\TocGenerator;
use Illuminate\Contracts\View\View;

class PostsShowController extends Controller
{
    public function __invoke(Post $post): View
    {
        $toc = (new TocGenerator)->getHtmlMenu($post->contents);

        return view('posts.show', [
            'post' => $post,
            'toc' => $toc,
        ]);
    }
}
