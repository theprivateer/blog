<?php

namespace Privateer\Basecms\Routes;

use Illuminate\Support\Facades\Route;
use Privateer\Basecms\Http\Controllers\CategoryController;
use Privateer\Basecms\Http\Controllers\PageController;
use Privateer\Basecms\Http\Controllers\PostController;
use Privateer\Basecms\Models\Post;

class BasecmsRoutes
{
    public static function register(): void
    {
        Route::get('/', [PageController::class, 'index'])->name('home');

        Route::get('/blog', [PostController::class, 'index'])->name('posts.index');
        Route::get('/blog/{post}', [PostController::class, 'show'])->name('posts.show');

        Route::get('/category/{category}', [CategoryController::class, 'show'])->name('categories.show');

        Route::get('/posts', function () {
            return redirect()->route('posts.index');
        });

        Route::get('/posts/{post}', function (Post $post) {
            return redirect()->route('posts.show', $post);
        });

        Route::get('/{page}', [PageController::class, 'show'])->name('pages.show');
    }
}
