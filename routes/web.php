<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// Основные маршруты
Route::get('/', [PageController::class, 'index'])->name('home');

// Карта сайта
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/{page}', [PageController::class, 'index'])->name('page.show');

