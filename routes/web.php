<?php

use App\Http\Controllers\MomentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;

Route::get('/', [PageController::class, 'index'])->name('home');

Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
Route::get('/notes/{note}', [NoteController::class, 'show'])->name('notes.show');

Route::get('/moments', [MomentController::class, 'index'])->name('moments.index');
Route::get('/moments/{moment}', [MomentController::class, 'show'])->name('moments.show');


Route::feeds();

Route::get('/{page}', [PageController::class, 'show'])->name('pages.show');
