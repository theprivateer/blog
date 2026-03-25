<?php

namespace Privateer\Basecms\Routes;

use Illuminate\Support\Facades\Route;
use Privateer\Basecms\Models\Post;

class BasecmsRoutes
{
    public static function register(): void
    {
        $pageController = (string) config('basecms.controllers.page');
        $postController = (string) config('basecms.controllers.post');
        $categoryController = (string) config('basecms.controllers.category');

        Route::get('/', [$pageController, 'index'])->name('home');

        Route::get('/blog', [$postController, 'index'])->name('posts.index');
        Route::get('/blog/{post}', [$postController, 'show'])->name('posts.show');

        Route::get('/category/{category}', [$categoryController, 'show'])->name('categories.show');

        Route::get('/posts', function () {
            return redirect()->route('posts.index');
        });

        Route::get('/posts/{post}', function (Post $post) {
            return redirect()->route('posts.show', $post);
        });

        Route::get('/{page}', [$pageController, 'show'])->name('pages.show');
    }
}
