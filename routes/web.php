<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnimeController;

Route::get('/', [AnimeController::class, 'index'])->name('home');
Route::get('/search', [AnimeController::class, 'search'])->name('search');
Route::get('/anime/{id}', [AnimeController::class, 'show'])->name('anime.show');
Route::get('/season', [AnimeController::class, 'season'])->name('season');
