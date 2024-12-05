<?php

use App\Http\Controllers\MicropubController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PagesShowController;
use App\Http\Controllers\PostsShowController;
use App\Http\Controllers\PostsIndexController;
use App\Http\Middleware\EnsureMicropubTokenIsValid;

Route::feeds();
Route::get('/micropub', [MicropubController::class, 'getCapabilities'])
    ->name('micropub')
    ->middleware(EnsureMicropubTokenIsValid::class);
Route::post('/micropub', [MicropubController::class, 'publish'])
    ->middleware(EnsureMicropubTokenIsValid::class);
Route::get('/', PostsIndexController::class)->name('posts.index');
Route::get('/post/{post}', PostsShowController::class)->name('posts.show');
Route::get('/{page}', PagesShowController::class)->name('pages.show');
