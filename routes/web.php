<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;

Route::get('/', [PageController::class, 'show'])->name('home');

Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '^(?!admin|login|register|password|api|sanctum|_ignition).*$')
    ->name('page.show');