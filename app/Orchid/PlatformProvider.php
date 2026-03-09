<?php

declare(strict_types=1);

namespace App\Orchid;

use App\Models\Page;
// use App\Services\MenuService;
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
    }

    /**
     * Register the application menu.
     */
    public function menu(): array
    {
        return [
            // Menu::make('Главная')
            //     ->icon('bs.house-door')
            //     ->title('Навигация')
            //     ->route('platform.page.edit', 1),

            // Динамическое меню сайта
            $this->buildSiteMenu(),

            Menu::make('Настройки')
                ->icon('gear')
                ->list([
                    Menu::make('Конфигурация')
                        ->icon('bs.sliders')
                        ->route('platform.settings'),
                    Menu::make('Шаблоны')
                        ->icon('list-task')
                        ->route('platform.template.list'),
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
                            // ... остальные примеры
                        ]),
                    Menu::make('Документация')
                        ->icon('bs.book')
                        ->route(config('platform.index')),
                ]),
        ];
    }

    /**
     * Построить динамическое меню сайта
     */
    private function buildSiteMenu(): Menu
    {
        $items = Cache::remember('admin.menu.site', 3600, function () {
            $pages = Page::where('in_menu', true)
                ->orderBy('parent')
                ->orderBy('menu_order')
                ->get();

            return $this->buildTree($pages);
        });

        return Menu::make('Меню сайта')
            ->icon('list')
            ->title('Структура сайта')
            ->list($this->formatForOrchid($items));
    }

    /**
     * Преобразовать плоский список в дерево
     */
    private function buildTree($items, $parentId = 0): array
    {
        $branch = [];

        foreach ($items as $item) {
            if ($item->parent == $parentId) {
                $children = $this->buildTree($items, $item->id);

                if (!empty($children)) {
                    $item->children = $children;
                }

                $branch[] = $item;
            }
        }

        return $branch;
    }

    /**
     * Преобразовать дерево страниц в структуру Menu::list()
     */
    private function formatForOrchid($items): array
    {
        return array_map(function ($item) {
            $menu = Menu::make($item->title)
                ->url(url($item->slug))
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
