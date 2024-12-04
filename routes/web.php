<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PagesShowController;
use App\Http\Controllers\PostsShowController;
use App\Http\Controllers\PostsIndexController;

Route::get('/', PostsIndexController::class)->name('posts.index');
Route::get('/post/{post}', PostsShowController::class)->name('posts.show');
Route::get('/{page}', PagesShowController::class)->name('pages.show');
