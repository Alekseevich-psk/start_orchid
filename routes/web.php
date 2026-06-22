<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeedbackController;


Route::get('/', [PageController::class, 'index'])->name('home');

Route::post('/feedback', [FeedbackController::class, 'send'])->name('feedback.send');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/{slug}', [PageController::class, 'index'])
    ->where('slug', '^(?!admin|login|register|password|api|sanctum|_ignition).*$')
    ->name('page.show');
