<?php

use App\Http\Controllers\CategoryController;
use App\Models\Post;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\MomentController;

Route::get('/', [PageController::class, 'index'])->name('home');

Route::get('/blog', [PostController::class, 'index'])->name('posts.index');
Route::get('/blog/{post}', [PostController::class, 'show'])->name('posts.show');

Route::get('/category/{category}', [CategoryController::class, 'show'])->name('categories.show');

Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
Route::get('/notes/{note}', [NoteController::class, 'show'])->name('notes.show');

Route::get('/moments', [MomentController::class, 'index'])->name('moments.index');
Route::get('/moments/{moment}', [MomentController::class, 'show'])->name('moments.show');

Route::feeds();

// Redirects from previous route structure
Route::get('/posts', function () {
    return redirect()->route('posts.index');
});

Route::get('/posts/{post}', function (Post $post) {
    return redirect()->route('posts.show', $post);
});

// Wildcard catch-all
Route::get('/{page}', [PageController::class, 'show'])->name('pages.show');
