<?php

use App\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Route;
use Privateer\Basecms\Routes\BasecmsRoutes;

Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
Route::get('/notes/{note}', [NoteController::class, 'show'])->name('notes.show');

Route::feeds();

BasecmsRoutes::register();
