# PageValidatorService

## Описание

`PageValidatorService` — сервис для валидации уникальности заголовка (`title`) и алиаса (`alias`) страниц. Он используется в админке для предотвращения дублирования страниц и обеспечения корректности URL.

Сервис интегрируется с Laravel Validation и выбрасывает исключение `ValidationException` при нарушении уникальности, автоматически перенаправляя пользователя на предыдущую страницу с ошибкой.

## Основные возможности

- **Проверка уникальности заголовка**: Метод `checkTitleUniqueness()` гарантирует, что заголовок страницы уникален.
- **Проверка уникальности алиаса**: Метод `checkAliasUniqueness()` гарантирует, что URL-адрес страницы уникален.
- **Исключение текущей страницы**: Параметр `$exceptId` позволяет исключить текущую страницу при редактировании.
- **Интеграция с Laravel Validation**: Использует `ValidationException` для автоматической обработки ошибок.
- **HTML-сообщения**: Алиасы возвращают HTML-ссылки на существующие страницы.

## Примеры использования

### В Screen-классе для создания страницы

```php
use App\Services\PageValidatorService;

class PageEditScreen extends Screen
{
    public function query(Page $page): array
    {
        return [
            'page' => $page,
        ];
    }

    public function layout(): array
    {
        return [
            Layout::view('platform::pages.form'),
        ];
    }

    public function save(Page $page, PageValidatorService $validator)
    {
        // Валидация уникальности
        $validator->checkTitleUniqueness($page->title);
        $validator->checkAliasUniqueness($page->alias);

        // Сохранение страницы
        $page->save();
    }
}
```

### В контроллере

```php
use App\Services\PageValidatorService;

class PageController extends Controller
{
    public function store(Request $request, PageValidatorService $validator)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'alias' => 'required|string|max:255',
        ]);

        try {
            $validator->checkTitleUniqueness($validated['title']);
            $validator->checkAliasUniqueness($validated['alias']);

            Page::create($validated);

            return redirect()->route('admin.pages.index')
                ->with('success', 'Страница создана');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }
}
```

### В orchid.php (пример интеграции)

```php
// В методе save() Screen-класса
public function save(Page $page, PageValidatorService $validator)
{
    $validator->checkTitleUniqueness($page->title);
    $validator->checkAliasUniqueness($page->alias);

    $page->save();

    return redirect()->route('admin.pages.index')
        ->with('success', 'Страница сохранена');
}
```

## Методы

### `__construct()`

**Конструктор** без параметров. Сервис регистрируется через autowiring в Laravel.

---

### `checkTitleUniqueness(string $title, ?int $exceptId = null): void`

**Публичный метод**, проверяет уникальность заголовка страницы.

**Алгоритм:**

1. Запрашивает страницы с таким же заголовком.
2. Если `$exceptId` задан, исключает страницу с этим ID (для редактирования).
3. Если страница найдена — вызывает `throwError()`.

**Параметры:**

- `$title` (string) — Заголовок страницы для проверки.
- `$exceptId` (int|null) — ID исключаемой страницы (по умолчанию `null`).

**Возвращает:** `void`

**Исключения:**
- `ValidationException` — если заголовок уже существует.

**Примеры:**
```php
// При создании страницы
$validator->checkTitleUniqueness('Новая страница');

// При редактировании страницы с ID = 5
$validator->checkTitleUniqueness('Измененный заголовок', 5);
```

---

### `checkAliasUniqueness(string $alias, ?int $exceptId = null): void`

**Публичный метод**, проверяет уникальность алиаса страницы.

**Алгоритм:**

1. Запрашивает страницы с таким же алиасом.
2. Если `$exceptId` задан, исключает страницу с этим ID (для редактирования).
3. Если страница найдена — вызывает `throwError()` с HTML-ссылкой на найденную страницу.

**Параметры:**

- `$alias` (string) — Алиас страницы для проверки.
- `$exceptId` (int|null) — ID исключаемой страницы (по умолчанию `null`).

**Возвращает:** `void`

**Исключения:**
- `ValidationException` — если алиас уже существует.

**Примеры:**
```php
// При создании страницы
$validator->checkAliasUniqueness('/novaya-stranitsa');

// При редактировании страницы с ID = 5
$validator->checkAliasUniqueness('/izmenennyy-alias', 5);
```

---

### `throwError(string $field, string $message): void`

**Приватный метод**, выбрасывает `ValidationException` с указанным сообщением.

**Алгоритм:**

1. Создает `ValidationException` с сообщением для указанного поля.
2. Настраивает `errorBag` и `redirectTo()` для автоматического перенаправления.

**Параметры:**

- `$field` (string) — Имя поля (например, `page.title`, `page.alias`).
- `$message` (string) — Сообщение об ошибке (может содержать HTML).

**Возвращает:** `void`

**Исключения:**
- `ValidationException` — всегда выбрасывает исключение.

## Особенности реализации

1. **HTML-сообщения для алиасов**: Метод `checkAliasUniqueness()` возвращает HTML-ссылку на существующую страницу, что удобно для администратора.
2. **Параметр `$exceptId`**: Позволяет редактировать страницы без ошибок уникальности для текущей записи.
3. **Интеграция с Laravel Validation**: Использует стандартные механизмы Laravel для обработки ошибок валидации.
4. **Удобство для админа**: Перенаправление на предыдущую страницу с сохранением введенных данных.

## Зависимости

- `App\Models\Page` — модель страниц.
- `Illuminate\Validation\ValidationException` — стандартное исключение валидации Laravel.

## Использование в проекте

`PageValidatorService` применяется в следующих местах:

- **Admin Pages**: Валидация при создании и редактировании страниц.
- **Custom Screens**: Любые Screen-классы, работающие с страницами.
- **API-контроллеры**: Валидация через исключения вместо ручной проверки.

## Рекомендации по использованию

- **Всегда проверяйте оба поля**: Заголовок и алиас должны быть уникальны.
- **Используйте `$exceptId` при редактировании**: Это предотвратит ошибку "страница существует" для текущей записи.
- **Обрабатывайте исключения**: В контроллерах используйте `try/catch` для `ValidationException`.
- **Документируйте правила**: В админке добавьте подсказки о требованиях к заголовкам и алиасам.

## Пример расширения

Добавление валидации уникальности `slug` (если он будет добавлен в модель):

```php
public function checkSlugUniqueness(string $slug, ?int $exceptId = null): void
{
    $query = Page::where('slug', $slug);
    if ($exceptId) {
        $query->where('id', '!=', $exceptId);
    }

    if ($query->exists()) {
        $existing = $query->first();
        $this->throwError('page.slug', "URL-слаг уже используется: «{$existing->slug}»");
    }
}
```

## Обработка исключений в Blade

```blade
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{!! $error !!}</li>
            @endforeach
        </ul>
    </div>
@endif
```

**Пример вывода ошибки для алиаса:**

```
Адрес /novaya-stranitsa уже используется: «Другая страница»
```

HTML-ссылка будет работать корректно в админке Orchid.