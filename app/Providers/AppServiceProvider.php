<?php

namespace App\Providers;

use App\Services\MenuService;
use App\Services\DemoJsonService;
use App\Services\SettingService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Регистрируем сервис настроек
        $this->app->singleton(SettingService::class, function ($app) {
            return new SettingService();
        });
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
