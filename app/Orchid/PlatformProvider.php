<?php

declare(strict_types=1);

namespace App\Orchid;

use App\Models\Page;
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

        // Авто очистка кэша при изменении страниц
        Page::created(fn() => Cache::forget('admin.menu.site'));
        Page::updated(fn() => Cache::forget('admin.menu.site'));
        Page::deleted(fn() => Cache::forget('admin.menu.site'));
    }

    /**
     * Register the application menu.
     */
    public function menu(): array
    {
        return [
            // Динамическое меню сайта — структура страниц
            $this->buildSiteMenu(),

            // Системные настройки
            Menu::make('Настройки')
                ->icon('gear')
                ->list([
                    Menu::make('Страницы')
                        ->icon('list-task')
                        ->route('platform.page.list'),
                    Menu::make('Шаблоны')
                        ->icon('list-task')
                        ->route('platform.template.list'),
                    Menu::make('Конфигурация')
                        ->icon('bs.sliders')
                        ->route('platform.settings'),
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
                    Menu::make('Примеры')
                        ->icon('bs.view-list')
                        ->list([
                            Menu::make('Sample Screen')
                                ->icon('bs.collection')
                                ->route('platform.example')
                                ->badge(fn() => 6),
                        ]),
                    Menu::make('Документация')
                        ->icon('bs.book')
                        ->route(config('platform.index')),
                ]),
        ];
    }

    /**
     * Построить динамическое меню сайта (для админки)
     */
    private function buildSiteMenu(): Menu
    {
        $items = Cache::remember('admin.menu.site', 3600, function () {
            $pages = Page::orderBy('parent')->orderBy('menu_order')->get();
            return $this->buildTree($pages);
        });

        return Menu::make('Меню сайта')
            ->icon('list')
            ->title('Управление страницами')
            ->list($this->formatForOrchid($items));
    }

    /**
     * Построить дерево с защитой от циклов
     */
    private function buildTree($items, $parentId = 0, $depth = 0): array
    {

        if ($depth > 10) {
            return [];
        }

        $branch = [];

        foreach ($items as $item) {
            if ($item->parent == $parentId) {
                $children = $this->buildTree($items, $item->id, $depth + 1);

                if (!empty($children)) {
                    $item->children = $children;
                }

                $branch[] = $item;
            }
        }

        return $branch;
    }

    /**
     * Преобразовать дерево в меню Orchid
     * Ссылки ведут в редактор страницы: platform.page.edit
     */
    private function formatForOrchid($items): array
    {
        return array_map(function ($item) {
            $menu = Menu::make($item->title)
                ->route('platform.page.edit', $item->id)
                ->icon($item->children ? 'folder' : 'file-text');

            if (!empty($item->children)) {
                $menu->list($this->formatForOrchid($item->children));
            }

            return $menu;
        }, $items);
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
