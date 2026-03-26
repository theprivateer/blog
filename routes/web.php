<?php

use App\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Route;
use Privateer\Basecms\Routes\BasecmsRoutes;
use Privateer\Basecms\Models\Post;

Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
Route::get('/notes/{note}', [NoteController::class, 'show'])->name('notes.show');

Route::feeds();

// Legacy redirects
Route::get('/posts', function () {
    return redirect()->route('posts.index');
});

Route::get('/posts/{post}', function (Post $post) {
    return redirect()->route('posts.show', $post);
});

// BaseCMS routing
BasecmsRoutes::register();
