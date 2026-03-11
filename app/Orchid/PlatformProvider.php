<?php

declare(strict_types=1);

namespace App\Orchid;

use App\Models\Page;
use App\Services\MenuService;
use Illuminate\Support\Facades\Cache;
use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // Очистка кэша меню при изменении страниц
        Page::created(fn() => Cache::forget('admin.menu.site'));
        Page::updated(fn() => Cache::forget('admin.menu.site'));
        Page::deleted(fn() => Cache::forget('admin.menu.site'));

        // Очистка кэша frontend-меню при изменении страниц
        Page::created(fn() => Cache::forget('site.menu.tree'));
        Page::updated(fn() => Cache::forget('site.menu.tree'));
        Page::deleted(fn() => Cache::forget('site.menu.tree'));
    }

    /**
     * Register the application menu.
     */
    public function menu(): array
    {
        return [
            // Динамическое меню сайта
            $this->buildSiteMenu(),

            // Системные настройки
            Menu::make('Настройки')
                ->icon('gear')
                ->list([
                    Menu::make('Шаблоны')
                        ->icon('list-task')
                        ->route('platform.template.list'),
                    Menu::make('Конфигурация')
                        ->icon('bs.sliders')
                        ->route('platform.settings'),
                    Menu::make('Менеджер полей')
                        ->icon('list-nested')
                        ->route('platform.field.list'),
                    Menu::make(__('Пользователи'))
                        ->icon('bs.people')
                        ->route('platform.systems.users')
                        ->permission('platform.systems.users')
                        ->title(__('Группы пользователей')),
                    Menu::make(__('Роли'))
                        ->icon('bs.shield')
                        ->route('platform.systems.roles')
                        ->permission('platform.systems.roles')
                        ->divider(),
                    Menu::make('Документация')
                        ->icon('bs.book')
                        ->route(config('platform.index')),
                ]),
        ];
    }

    /**
     * Построить динамическое меню сайта (плоское)
     */
    private function buildSiteMenu(): Menu
    {
        $menuItems = app(MenuService::class)->getAdminMenuItems();

        return Menu::make('Меню сайта')
            ->icon('list')
            ->title('Управление страницами')
            ->list($menuItems)
            ->route($menuItems ? 'platform.page.list' : 'platform.main');
    }

    /**
     * Register permissions for the application.
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
        ];
    }
}
