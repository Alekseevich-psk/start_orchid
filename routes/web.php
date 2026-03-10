<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// Основные маршруты
Route::get('/', [PageController::class, 'index'])->name('home');

// Карта сайта
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/{slug}', [PageController::class, 'index'])
    ->where('slug', '^(?!admin|login|register|password|api|sanctum|_ignition).*$')
    ->name('page.show');
