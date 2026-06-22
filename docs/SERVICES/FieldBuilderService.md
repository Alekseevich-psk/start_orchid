# FieldBuilderService

## Описание

`FieldBuilderService` — сервис для построения форм Orchid Screen на основе полей шаблонов и страниц. Он автоматически генерирует поля ввода (Input, TextArea, Select, CheckBox, Picture, Upload) в зависимости от типа поля (text, textarea, select, checkbox, image, file).

Сервис используется в админке для динамического создания форм редактирования страниц и шаблонов, позволяя менеджерам заполнять контент без правки кода.

## Основные возможности

- **Динамическая генерация форм**: Построение полей на основе моделей `Field`, `Page` и `Template`.
- **Приоритет полей**: Поля страницы переопределяют поля шаблона.
- **Поддержка типов**: text, textarea, checkbox, select, image, file.
- **Обработка опций Select**: Автоматический парсинг опций для выпадающего списка.
- **Гибкость**: Легко расширяемый для новых типов полей.

## Примеры использования

### В Screen-классе для редактирования страницы

```php
use App\Services\FieldBuilderService;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screens\Screen;

class PageEditScreen extends Screen
{
    public $page;

    public function query(Page $page): array
    {
        $this->page = $page;

        return [
            'page' => $page,
            'formFields' => app(FieldBuilderService::class)->build($page),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::view('platform::pages.form'),
            Layout::rows([
                'formFields' => [
                    'type' => 'form',
                    'method' => 'POST',
                    'action' => route('admin.pages.update', $this->page),
                ],
            ]),
        ];
    }

    public function save(Page $page, FieldBuilderService $builder)
    {
        // Сохранение данных из $request->all()
        // Поле page.blocks будет содержать значения всех полей
    }
}
```

### В Blade-шаблоне

```blade
@extends('platform::layouts.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.pages.update', $page) }}">
                        @csrf
                        @method('PUT')

                        @foreach ($formFields as $field)
                            {{ $field }}
                        @endforeach

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
```

### В orchid.php (пример интеграции)

```php
// В методе query() Screen-класса
public function query(Page $page): array
{
    return [
        'page' => $page,
        'formFields' => app(FieldBuilderService::class)->build($page),
    ];
}
```

## Методы

### `__construct()`

**Конструктор** без параметров. Сервис регистрируется через autowiring в Laravel.

---

### `build(Page $page): array`

**Публичный метод**, строит массив полей Orchid на основе полей шаблона и страницы.

**Алгоритм:**

1. Инициализирует пустой `Collection` для полей.
2. Загружает поля шаблона (если `template_id` установлен).
3. Загружает поля страницы (если `id` страницы установлен).
4. Применяет приоритет: поля страницы переопределяют поля шаблона (по `field_id`).
5. Для каждого поля вызывает соответствующий метод генерации поля Orchid.
6. Возвращает массив объектов полей.

**Параметры:**
- `$page` (Page) — Экземпляр модели `Page`.

**Возвращает:**
- `array` — Массив объектов полей Orchid (Input, TextArea, Select и т.д.).

**Пример:**
```php
$fields = $builder->build($page);
// [
//     Input::make('page.blocks.title'),
//     TextArea::make('page.blocks.content'),
//     Select::make('page.blocks.layout')->options([...]),
// ]
```

---

### `parseOptions(?string $raw): array`

**Приватный метод**, парсит строку опций для Select-поля.

**Формат строки:**
```
"Опция 1||Опция 2||Опция 3"
```

**Алгоритм:**
1. Делит строку по `||`.
2. Очищает пробелы через `trim()`.
3. Фильтрует пустые значения.
4. Создает ассоциативный массив: `['Опция 1' => 'Опция 1', ...]`.

**Параметры:**
- `$raw` (string|null) — Сырая строка опций.

**Возвращает:**
- `array` — Ассоциативный массив опций.

**Пример:**
```php
$builder->parseOptions('Да||Нет||Не знаю');
// ['Да' => 'Да', 'Нет' => 'Нет', 'Не знаю' => 'Не знаю']
```

## Поддерживаемые типы полей

| Тип    | Поля Orchid | Описание |
|--------|-------------|----------|
| `text` | `Input::make()` | Однострочный текст |
| `textarea` | `TextArea::make()->rows(5)` | Многострочный текст (5 строк) |
| `checkbox` | `CheckBox::make()` | Флажок (true/false) |
| `select` | `Select::make()->options([...])` | Выпадающий список (из `field.options`) |
| `image` | `Picture::make()->maxFiles(1)` | Одно изображение |
| `file` | `Upload::make()->maxFiles(1)` | Один файл |

## Структура данных

### Таблица `fields`

| Поле | Тип | Описание |
|------|-----|----------|
| `id` | bigint | ID поля |
| `field_id` | string | Уникальный ID поля (например, `title`, `content`) |
| `model_type` | string | `template` или `page` |
| `model_id` | bigint | ID связанной модели |
| `title` | string | Заголовок поля (для админки) |
| `type` | string | Тип поля (text, textarea, select, checkbox, image, file) |
| `options` | text | Опции для Select (разделены `||`) |

### Пример записи поля

```php
// Поле для заголовка (из шаблона)
[
    'field_id' => 'title',
    'model_type' => 'template',
    'model_id' => 1,
    'title' => 'Заголовок',
    'type' => 'text',
    'options' => null,
]

// Поле для контента (из шаблона)
[
    'field_id' => 'content',
    'model_type' => 'template',
    'model_id' => 1,
    'title' => 'Контент',
    'type' => 'textarea',
    'options' => null,
]

// Поле для layout (из шаблона)
[
    'field_id' => 'layout',
    'model_type' => 'template',
    'model_id' => 1,
    'title' => 'Макет',
    'type' => 'select',
    'options' => 'Default||Sidebar||Full Width',
]
```

## Особенности реализации

1. **Приоритет полей**: Поля страницы переопределяют поля шаблона через `reject()` и `concat()`.
2. **Генерация имён полей**: Используется формат `page.blocks.{field_id}` для удобной обработки в контроллерах.
3. **Обработка ошибок**: Неподдерживаемые типы полей генерируют предупреждающее поле с `help()`.
4. **Легковесность**: Нет кэширования — поля строятся динамически для каждой страницы.

## Зависимости

- `App\Models\Field` — модель полей.
- `App\Models\Page` — модель страниц.
- `Orchid\Screen\Fields\*` — все типы полей Orchid.

## Использование в проекте

`FieldBuilderService` применяется в следующих местах:

- **Admin Pages**: Создание форм редактирования страниц.
- **Admin Templates**: Создание форм редактирования шаблонов.
- **Custom Screens**: Любые Screen-классы, требующие динамических полей.

## Рекомендации по использованию

- **Используйте `field_id` как ключ**: Это гарантирует уникальность и правильное переопределение полей.
- **Тестируйте типы полей**: При добавлении нового типа убедитесь, что он поддерживается в `build()`.
- **Документируйте поля**: В админке добавляйте `help()` для сложных полей.
- **Ограничивайте размеры**: Для `TextArea` используйте `rows()` для управления высотой.

## Пример расширения

Добавление нового типа поля `richtext`:

```php
// В FieldBuilderService::build()
case 'richtext':
    $formFields[] = TextArea::make($name)
        ->title($field->title)
        ->attributes(['class' => 'richtext']);
    break;
```

```blade
<!-- В Blade-шаблоне -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.richtext').forEach(el => {
            // Инициализация редактора (например, TinyMCE)
        });
    });
</script>
```