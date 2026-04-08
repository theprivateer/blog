<?php

use App\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Route;
use Privateer\Basecms\Models\Post;
use Privateer\Basecms\Routes\BasecmsRoutes;
use Privateer\Basecms\Services\SiteManager;

Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
Route::get('/notes/{note}', [NoteController::class, 'show'])->name('notes.show');

Route::feeds();

// Legacy redirects
Route::get('/posts', function () {
    return redirect()->route('posts.index');
});

Route::get('/posts/{post}', function (string $post) {
    $resolvedPost = Post::query()
        ->forSite(app(SiteManager::class)->siteForRequest())
        ->where('slug', $post)
        ->firstOrFail();

    return redirect()->route('posts.show', $resolvedPost);
});

// BaseCMS routing
BasecmsRoutes::register();
