<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MenuService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share menu tree with views
        view()->composer('*', function ($view) {
            $menuTree = app(MenuService::class)->getFrontendMenuTree();
            $view->with('siteMenuTree', $menuTree);
        });
    }
}
