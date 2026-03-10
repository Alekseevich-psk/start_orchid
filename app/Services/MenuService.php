<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Orchid\Screen\Actions\Menu;


class MenuService
{
    /**
     * Построить список пунктов меню для админки
     * Возвращает массив объектов Menu
     */
    public function getAdminMenuItems(): array
    {
        if (!Schema::hasTable('pages')) {
            return [];
        }

        return Page::orderBy('parent_id')
            ->orderBy('menu_order')
            ->get()
            ->map(function ($item) {
                $icon = $this->getIconForItem($item);

                // Создаем объект Menu только для корневых элементов
                if (!$item->parent_id) {
                    return Menu::make($item->title)
                        ->icon($icon)
                        ->route('platform.page.edit', $item->id);
                }

                return null; // Игнорируем дочерние элементы
            })
            ->filter(fn($item) => $item !== null) // Убираем null-значения
            ->toArray();
    }

    /**
     * Получить иконку для пункта
     */
    private function getIconForItem($item): string
    {
        if ($item->is_category) {
            return 'folder';
        }

        if ($item->ico) {
            return $item->ico;
        }

        if ($item->template_id && $item->relationLoaded('template') && $item->template && $item->template->icon) {
            return $item->template->icon;
        }

        return 'file-text';
    }

    /**
     * Построить дерево для сайта (frontend)
     * Возвращает структуру для фронтенда и хлебных крошек
     */
    public function getFrontendMenuTree()
    {
        return Cache::remember('site.menu.tree', 3600, function () {
            if (!Schema::hasTable('pages')) {
                return [];
            }

            $pages = Page::published()->inMenu()->with('template')->orderBy('menu_order')->get();
            $tree = $this->buildTree($pages);
            
            // Обеспечиваем, что $tree всегда массив
            if (!is_array($tree)) {
                $tree = [];
            }

            // Глобальная переменная для шаринга с middleware и представлениями
            $GLOBALS['siteMenuTree'] = $tree;

            // Возвращаем массив
            return $tree;
        });
    }

    /**
     * Построить рекурсивное дерево из плоского списка
     */
    private function buildTree($items, $parentId = 0, $depth = 0): array
    {
        if ($depth > 10) {
            return [];
        }

        $branch = [];

        foreach ($items as $item) {
            if ($item->parent_id == $parentId) {
                $children = $this->buildTree($items, $item->id, $depth + 1);

                $branchItem = [
                    'id' => $item->id,
                    'title' => $item->title,
                    'slug' => $item->slug,
                    'url' => url($item->slug),
                    'icon' => $this->getIconForItem($item),
                    'indexed' => $item->indexed,
                ];

                if (!empty($children)) {
                    $branchItem['children'] = $children;
                }

                $branch[] = $branchItem;
            }
        }

        return $branch;
    }
}
