<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MicropubController;
use App\Http\Controllers\PostsShowController;
use App\Http\Controllers\PostsIndexController;
use App\Http\Controllers\SlashesShowController;
use App\Http\Controllers\SlashesIndexController;
use App\Http\Middleware\EnsureMicropubTokenIsValid;

Route::feeds();
Route::get('/micropub', [MicropubController::class, 'getCapabilities'])
    ->name('micropub')
    ->middleware(EnsureMicropubTokenIsValid::class);
Route::post('/micropub', [MicropubController::class, 'publish'])
    ->middleware(EnsureMicropubTokenIsValid::class);
Route::get('/', PostsIndexController::class)->name('posts.index');
Route::get('/post/{post}', PostsShowController::class)->name('posts.show');
Route::get('/slashes', SlashesIndexController::class)->name('slashes.index');

// Common redirects
Route::get('hello', fn () => redirect()->to('contact'));
Route::get('subscribe', fn () => redirect()->to('follow'));
Route::get('feeds', fn () => redirect()->to('follow'));
Route::get('next', fn () => redirect()->to('someday'));
Route::get('log', fn () => redirect()->to('changelog'));

Route::get('/{slash}', SlashesShowController::class)->name('slashes.show');
