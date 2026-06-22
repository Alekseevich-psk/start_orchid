<?php

namespace App\Providers;

use App\Services\MenuService;
use App\Services\DemoJsonService;
use Illuminate\Support\ServiceProvider;

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
            $demoJson = new DemoJsonService();

            $view->with([
                'siteMenuTree' => $menuTree,
                'demoJson' => $demoJson
            ]);
        });
    }
}
