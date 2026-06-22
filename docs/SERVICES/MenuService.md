# MenuService

## Описание

`MenuService` — центральный сервис для работы с навигацией в приложении. Он управляет построением меню для админки и фронтенда, генерацией хлебных крошек, перестроением путей страниц и рекурсивным построением деревьев.

Сервис интегрирован с моделью `Page`, использует кэширование для оптимизации производительности и обеспечивает единообразную структуру навигации в админке и на сайте.

## Основные возможности

- **Построение админ-меню**: Создание пунктов меню для интерфейса Orchid.
- **Построение фронтенд-меню**: Генерация деревьев для публичного сайта с кэшированием (1 час).
- **Хлебные крошки**: Поддержка как админских (`buildAdminPageBreadcrumbs`), так и публичных (`buildBreadcrumbs`) крошек.
- **Перестроение путей**: Автоматическое обновление slug'ов всех страниц при изменении структуры (`rebuildAllPagePaths`).
- **Генерация уникальных путей**: Защита от дублирования slug'ов с автоматическим добавлением суффиксов (`-1`, `-2`, и т.д.).
- **Учет `in_slug_path`**: Умная генерация путей, пропускающая страницы с отключенным `in_slug_path`.

## Примеры использования

### В контроллере

```php
use App\Services\MenuService;

class PageController extends Controller
{
    public function index(MenuService $menu)
    {
        // Получить пункты меню для админки
        $adminItems = $menu->getAdminMenuItems();

        // Построить дерево для фронтенда
        $tree = $menu->getFrontendMenuTree();

        // Построить хлебные крошки для страницы
        $breadcrumbs = $menu->buildBreadcrumbs($page);

        return view('pages.index', [
            'adminMenu' => $adminItems,
            'menuTree' => $tree,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
```

### В Screen-классе Orchid

```php
use App\Models\Page;
use App\Services\MenuService;

class PageEditScreen extends Screen
{
    public function layout(): array
    {
        return [
            Layout::sidebar()
                ->title(__('Pages'))
                ->route('platform.page.list')
                ->canSee($this->can)
                ->render(function (MenuService $menu) {
                    return $menu->getAdminMenuItems();
                }),
        ];
    }
}
```

### В Blade-шаблоне (для фронтенда)

```blade
@php
    use App\Services\MenuService;
    $menu = app(MenuService::class);
    $tree = $menu->getFrontendMenuTree();
@endphp

<nav class="main-menu">
    @foreach ($tree as $item)
        <a href="{{ $item['url'] }}" class="menu-item">
            {{ $item['title'] }}
        </a>
        
        @if (isset($item['children']) && count($item['children']))
            <ul class="submenu">
                @foreach ($item['children'] as $child)
                    <li><a href="{{ $child['url'] }}">{{ $child['title'] }}</a></li>
                @endforeach
            </ul>
        @endif
    @endforeach
</nav>
```

### В админке (с хлебными крошками)

```blade
<x-orchid-layout>
    <x-slot name="header">
        <h1 class="header-title">
            @foreach ($menu->buildAdminPageBreadcrumbs($page, 'edit') as $crumb)
                @if ($crumb['url'])
                    <a href="{{ $crumb['url'] }}">{!! $crumb['title'] !!}</a> /
                @else
                    <span class="active">{!! $crumb['title'] !!}</span>
                @endif
            @endforeach
        </h1>
    </x-slot>

    <div class="form-group">
        <!-- Форма редактирования -->
    </div>
</x-orchid-layout>
```

## Методы

### `getAdminMenuItems(): array`

**Публичный метод**, возвращает массив объектов `Menu` для админки Orchid.

**Особенности:**
- Проверяет наличие таблицы `pages`.
- Игнорирует дочерние элементы (только корневые страницы).
- Использует `getIconForItem()` для определения иконок.

**Возвращает:**
- `array` — Массив объектов `Orchid\Screen\Actions\Menu`.

---

### `getIconForItem($item): string`

**Приватный метод**, определяет иконку для пункта меню.

**Алгоритм:**
1. Если `is_category` → `'folder'`
2. Если есть поле `ico` → `$item->ico`
3. Если есть загруженная связь `template` и есть иконка → `$item->template->icon`
4. Иначе → `'file-text'`

**Параметры:**
- `$item` (Page) — Экземпляр модели `Page`.

**Возвращает:**
- `string` — Название иконки (например, `'folder'`, `'file-text'`).

---

### `getFrontendMenuTree(): array`

**Публичный метод**, возвращает дерево меню для фронтенда с кэшированием на 1 час.

**Особенности:**
- Кэширует результат под ключ `'site.menu.tree'`.
- Использует `published()` и `inMenu()` scope.
- Загружает связь `template`.
- Сохраняет результат в глобальную переменную `$GLOBALS['siteMenuTree']`.

**Возвращает:**
- `array` — Структура:
  ```php
  [
      [
          'id' => 1,
          'title' => 'Главная',
          'slug' => '/',
          'url' => '/',
          'icon' => 'home',
          'indexed' => true,
          'children' => [
              // вложенные элементы
          ]
      ],
      // ...
  ]
  ```

---

### `buildTree($items, $parentId = 0, $depth = 0): array`

**Приватный метод**, рекурсивно строит дерево из плоского списка.

**Параметры:**
- `$items` (Collection|array) — Список страниц.
- `$parentId` (int) — ID родителя (по умолчанию `0`).
- `$depth` (int) — Текущая глубина вложенности (защита от бесконечной рекурсии: макс. 10).

**Возвращает:**
- `array` — Структурированное дерево.

---

### `buildAdminPageBreadcrumbs(?Page $page, string $action): Collection`

**Публичный метод**, строит хлебные крошки для админки.

**Параметры:**
- `$page` (Page|null) — Текущая страница (для `edit`).
- `$action` (string) — `'create'` или `'edit'`.

**Возвращает:**
- `Collection<int, array{title: string, url?: string}>` — Коллекция крошек.

**Пример для `create`:**
```php
[
    ['title' => 'Страницы', 'url' => '/admin/pages'],
    ['title' => 'Создать', 'url' => null],
]
```

**Пример для `edit`:**
```php
[
    ['title' => 'Страницы', 'url' => '/admin/pages'],
    ['title' => 'Каталог', 'url' => '/admin/pages/5/edit'],
    ['title' => 'Текущая страница', 'url' => null],
    ['title' => 'Редактирование', 'url' => null],
]
```

---

### `buildBreadcrumbs(Page $page): array`

**Публичный метод**, строит хлебные крошки для публичного сайта.

**Особенности:**
- Кэшируется под ключ `breadcrumbs.{page_id}` на 1 час.
- Учитывает только страницы с `in_slug_path = true`.
- Генерирует абсолютные URL через `url()`.

**Параметры:**
- `$page` (Page) — Целевая страница.

**Возвращает:**
- `array` — Массив крошек:
  ```php
  [
      ['title' => 'Главная', 'url' => 'https://site.com/'],
      ['title' => 'Каталог', 'url' => 'https://site.com/catalog'],
      ['title' => 'Текущая страница', 'url' => 'https://site.com/catalog/item'],
  ]
  ```

---

### `buildMenu(?int $parentId = null): Collection`

**Публичный метод**, возвращает коллекцию страниц для построения меню.

**Особенности:**
- Загружает только опубликованные страницы (`is_published = true`).
- Только страницы с `in_menu = true`.
- Загружает вложенные связи `children` с теми же условиями.

**Параметры:**
- `$parentId` (int|null) — ID родителя (`null` = корневые элементы).

**Возвращает:**
- `Collection` — Список страниц с вложенными детьми.

---

### `rebuildAllPagePaths()`

**Публичный метод**, перестраивает slug'и всех страниц.

**Особенности:**
- Проходит по всем страницам в порядке `parent_id`.
- Использует `generateFullPath()` для генерации уникальных путей.
- Очищает кэш `'site.menu.tree'` и весь кэш (`Cache::flush()`).

---

### `collectPathSegments(Page $page, $allPages, array $pathMap, array &$segments): void`

**Приватный метод**, рекурсивно собирает сегменты пути от корня к родителю.

**Особенности:**
- Добавляет только страницы с `in_slug_path = true`.
- Использует `$pathMap` для получения актуальных slug'ов.

**Параметры:**
- `$page` (Page) — Текущая страница.
- `$allPages` (Collection) — Коллекция всех страниц.
- `$pathMap` (array) — Карта ID → slug.
- `$segments` (array, по ссылке) — Накапливаемый массив сегментов.

---

### `generateFullPath(string $localSlug, ?int $parentId, ?int $currentId): string`

**Публичный метод**, генерирует полный slug с учетом родительских путей.

**Алгоритм:**
1. Если нет родителя → возвращает `$localSlug`.
2. Иначе → вызывает `buildSlugPath()` для сбора сегментов.
3. Проверяет уникальность: если slug занят → добавляет суффикс `-1`, `-2`, и т.д.

**Параметры:**
- `$localSlug` (string) — Локальный slug текущей страницы.
- `$parentId` (int|null) — ID родителя.
- `$currentId` (int|null) — ID текущей страницы (для исключения из проверки).

**Возвращает:**
- `string` — Полный путь (например, `'catalog/electronics/phones'`).

---

### `buildSlugPath(?int $parentId, array &$segments): void`

**Приватный метод**, рекурсивно строит цепочку slug'ов.

**Особенности:**
- Идёт от корня к родителю.
- Добавляет только страницы с `in_slug_path = true`.
- Использует `slug`, если есть, иначе `alias`.

**Параметры:**
- `$parentId` (int|null) — ID родителя.
- `$segments` (array, по ссылке) — Накапливаемый массив сегментов.

---

### `generateAlias(string $title, ?int $pageId = null): string`

**Публичный метод**, генерирует slug из заголовка.

**Особенности:**
- Для главной страницы (`id = 1`) всегда возвращает `'/'.`
- Использует `Str::slug()`.

**Параметры:**
- `$title` (string) — Заголовок страницы.
- `$pageId` (int|null) — ID страницы.

**Возвращает:**
- `string` — Slug (например, `'my-page-title'`).

## Особенности реализации

1. **Кэширование**:
   - `getFrontendMenuTree()` — 1 час (`site.menu.tree`).
   - `buildBreadcrumbs()` — 1 час (`breadcrumbs.{page_id}`).
2. ** protection от бесконечной рекурсии** — проверка `$depth > 10` в `buildTree()`.
3. **Уникальность путей** — автоматическое добавление суффиксов при дублировании.
4. **Глобальный доступ** — результат `getFrontendMenuTree()` сохраняется в `$GLOBALS['siteMenuTree']`.
5. **Безопасные проверки** — `Schema::hasTable('pages')` во всех методах.

## Зависимости

- `App\Models\Page` — модель страниц.
- `Orchid\Screen\Actions\Menu` — класс для админ-меню.
- `Illuminate\Support\Facades\Cache` — кэширование.
- `Illuminate\Support\Facades\Schema` — проверка таблицы.
- `Illuminate\Support\Str` — генерация slug.

## Использование в проекте

`MenuService` применяется в следующих местах:

- **Admi**n: Построение бокового меню в `PageEditScreen`.
- **Frontend**: Генерация главного меню, хлебных крошек, URL-путей.
- **Middleware**: Использование `$GLOBALS['siteMenuTree']` для доступа в шаблонах.
- **Команды Artisan**: Перестроение путей при миграциях или ручном вызове.

## Рекомендации по использованию

- **Всегда кэшируйте**: Используйте `getFrontendMenuTree()` вместо прямого запроса к `Page`.
- **Проверяйте уникальность**: При ручном создании страниц используйте `generateAlias()`.
- **Перестраивайте пути**: После миграций или массовых изменений вызывайте `rebuildAllPagePaths()`.
- **Избегайте бесконечных циклов**: В Blade-шаблонах проверяйте `isset($item['children'])`.