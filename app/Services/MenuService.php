<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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
                    'url' => $item->slug === '/' ? '/' : '/' . ltrim($item->slug, '/'),
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

    /**
     * Строит хлебные крошки для редактирования страницы в админке
     * Все ссылки ведут внутрь админки
     *
     * @param Page|null $page
     * @param string $action 'create'|'edit'
     * @return Collection<int, array{title: string, url?: string}>
     */
    public function buildAdminPageBreadcrumbs(?Page $page, string $action): Collection
    {
        $breadcrumbs = collect();

        // 1. "Страницы" — список в админке
        $breadcrumbs->push([
            'title' => 'Страницы',
            'url' => route('platform.page.list')
        ]);

        if ($action === 'create') {
            $breadcrumbs->push([
                'title' => 'Создать',
                'url' => null
            ]);
        } elseif ($action === 'edit' && $page) {
            // Собираем путь от корня до родителя текущей страницы — но в админке!
            $current = $page->parent;
            $path = [];

            while ($current) {
                $path[] = [
                    'title' => $current->title,
                    'url' => route('platform.page.edit', $current->id) // ← Админ-URL!
                ];
                $current = $current->parent;
            }

            // Добавляем в обратном порядке (от корня)
            $breadcrumbs = $breadcrumbs->merge(collect(array_reverse($path)));

            // Текущая страница — без ссылки (активна)
            $breadcrumbs->push([
                'title' => $page->title,
                'url' => null
            ]);

            // Экшен — редактирование
            $breadcrumbs->push([
                'title' => 'Редактирование',
                'url' => null
            ]);
        }

        return $breadcrumbs;
    }

    /**
     * Построить хлебные крошки для указанной страницы
     */
    public function buildBreadcrumbs(Page $page): array
    {
        return Cache::remember("breadcrumbs.{$page->id}", 3600, function () use ($page) {
            $breadcrumbs = [];

            // Собираем путь от корня до родителя (включая только in_slug_path = true)
            $path = [];
            $current = $page->parent;

            while ($current) {
                if ($current->in_slug_path ?? true) {
                    $path[] = [
                        'title' => $current->title,
                        'url'   => url($current->slug),
                    ];
                }
                $current = $current->parent;
            }

            // Разворачиваем путь: от корня к родителю
            $breadcrumbs = array_reverse($path);

            // Добавляем текущую страницу
            $breadcrumbs[] = [
                'title' => $page->title,
                'url'   => url($page->slug),
            ];

            return $breadcrumbs;
        });
    }

    /**
     * Построить древовидное меню (например, для шаблона)
     */
    public function buildMenu(?int $parentId = null): Collection
    {
        return Page::where('is_published', true)
            ->where('in_menu', true)
            ->where('parent_id', $parentId)
            ->orderBy('menu_order')
            ->orderBy('title')
            ->with(['children' => fn($q) => $q->published()->inMenu()->ordered()])
            ->get();
    }

    /**
     * Перестроить slug'и всех страниц с учётом in_slug_path
     */
    public function rebuildAllPagePaths()
    {
        $pages = Page::orderBy('parent_id')->get();
        $pathMap = [];

        foreach ($pages as $page) {
            $localSlug = $page->alias ?: Str::slug($page->title);

            $newPath = $this->generateFullPath(
                $localSlug,
                $page->parent_id,
                $page->id
            );

            $page->slug = $newPath;
            $page->saveQuietly();

            $pathMap[$page->id] = $newPath;
        }

        Cache::forget('site.menu.tree');
        Cache::flush(); // или точечно
    }

    /**
     * Рекурсивно собирает сегменты пути, учитывая in_slug_path
     */
    private function collectPathSegments(Page $page, $allPages, array $pathMap, array &$segments): void
    {
        if (!$page->parent_id) {
            return;
        }

        $parent = $allPages->firstWhere('id', $page->parent_id);

        if (!$parent) {
            return;
        }

        // Рекурсивно идём к корню
        $this->collectPathSegments($parent, $allPages, $pathMap, $segments);

        // Добавляем только если in_slug_path включён
        if ($parent->in_slug_path ?? true) {
            $parentSlug = $pathMap[$parent->id] ?? $parent->slug;
            $segments[] = trim($parentSlug, '/');
        }
    }

    /**
     * Генерирует полный путь: parent/path + local-slug
     */
    public function generateFullPath(string $localSlug, ?int $parentId, ?int $currentId): string
    {
        $path = $localSlug;

        // Рекурсивно собираем путь от корня, пропуская страницы с in_slug_path = false
        if ($parentId) {
            $segments = [];

            $this->buildSlugPath($parentId, $segments);

            if (!empty($segments)) {
                $path = implode('/', $segments) . '/' . $localSlug;
            }
        }

        // Проверяем уникальность пути
        $query = Page::where('slug', $path);
        if ($currentId) {
            $query->where('id', '!=', $currentId);
        }

        if ($query->exists()) {
            $counter = 1;
            while (Page::where('slug', "{$path}-{$counter}")->where('id', '!=', $currentId)->exists()) {
                $counter++;
            }
            $path = "{$path}-{$counter}";
        }

        return $path;
    }

    /**
     * Рекурсивно строит цепочку slug'ов только для страниц с in_slug_path = true
     */
    private function buildSlugPath(?int $parentId, array &$segments): void
    {
        if (!$parentId) {
            return;
        }

        $parent = Page::find($parentId);

        if (!$parent) {
            return;
        }

        // Рекурсивно идём к корню
        $this->buildSlugPath($parent->parent_id, $segments);

        // Добавляем текущий slug/alias только если in_slug_path включён
        if ($parent->in_slug_path ?? true) {
            $slugPart = $parent->slug ?? $parent->alias;
            $segments[] = trim($slugPart, '/');
        }
    }

    public function generateAlias(string $title, ?int $pageId = null): string
    {
        if ($pageId === 1) {
            return '/';
        }

        return Str::slug($title ?: 'page');
    }
}
