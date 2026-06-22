# AttachmentUrlResolver

## Описание

`AttachmentUrlResolver` — сервис для автоматической замены ID вложений Orchid на их реальные URL-адреса. Этот сервис используется везде, где необходимо обработать данные, содержащие ссылки на вложения (изображения, файлы), и заменить их ID на публичные URL.

Основная цель сервиса — избежать множества отдельных запросов к базе данных при получении URL каждого вложения, объединяя их в один эффективный запрос.

## Основные возможности

- **Автоматическое обнаружение**: Рекурсивно находит все поля, начинающиеся с `file_` и `files_`, содержащие массивы ID.
- **Эффективная загрузка**: Загружает все URL одним запросом к таблице `attachments`.
- **Кэширование в рамках запроса**: Загрузка URL происходит только один раз за запрос (внутри одного экземпляра сервиса).
- **Безопасная замена**: Заменяет ID только в полях, соответствующих шаблону `file_*` или `files_*`, и только если значения похожи на массивы ID.

## Примеры использования

### В контроллере

```php
use App\Services\AttachmentUrlResolver;

class PageController extends Controller
{
    public function show(AttachmentUrlResolver $resolver)
    {
        $pageData = [
            'file_cover' => [123],
            'files_gallery' => [456, 789],
            'content' => 'Текст страницы',
            'metadata' => [
                'file_thumbnail' => [101]
            ]
        ];

        $resolvedData = $resolver->resolve($pageData);

        // $resolvedData['file_cover'] => '/storage/attachments/cover.jpg'
        // $resolvedData['files_gallery'] => ['/storage/attachments/img1.jpg', '/storage/attachments/img2.jpg']
        // $resolvedData['metadata']['file_thumbnail'] => '/storage/attachments/thumb.jpg'

        return view('page.show', ['data' => $resolvedData]);
    }
}
```

### В Blade-шаблоне (через helper)

```blade
@php
    use App\Services\AttachmentUrlResolver;
    $resolver = app(AttachmentUrlResolver::class);
    $resolvedData = $resolver->resolve($page->content);
@endphp

<img src="{{ $resolvedData['file_cover'] }}" alt="Обложка">
```

### В интеграции с другими сервисами

`AttachmentUrlResolver` используется внутри `DemoJsonService` для обработки данных из JSON:

```php
// В DemoJsonService::loadData()
return (new AttachmentUrlResolver)->resolve($rawData);
```

## Методы

### `resolve(array $data): array`

**Основной метод**, который принимает массив данных и возвращает его копию со всеми ID вложений, заменёнными на URL.

**Параметры:**
- `$data` (array) — Массив данных, в котором нужно заменить ID вложений.

**Возвращает:**
- `array` — Копия массива с заменёнными ID на URL.

### `collectAndLoadUrls(array $data): void`

**Приватный метод**, который рекурсивно собирает все ID из массива и загружает их URL в `$this->urlMap`.

**Параметры:**
- `$data` (array) — Мас��ив данных для анализа.

### `collectIds(array $data, array &$ids = []): array`

**Приватный метод**, который рекурсивно находит все ID в массиве.

**Параметры:**
- `$data` (array) — Массив данных для анализа.
- `$ids` (array, ссылка) — Накапливаемый массив ID.

**Возвращает:**
- `array` — Массив уникальных ID.

### `replaceIdsWithUrls(&$data): void`

**Приватный метод**, который заменяет ID на URL в массиве по ссылке.

**Параметры:**
- `$data` (array, по ссылке) — Массив данных, в котором нужно произвести замену.

### `isFileField(string $key): bool`

**Приватный метод**, проверяет, является ли ключ полем файла.

**Параметры:**
- `$key` (string) — Ключ массива.

**Возвращает:**
- `bool` — `true`, если ключ начинается с `file_` или `files_`.

### `looksLikeIds(mixed $value): bool`

**Приватный метод**, проверяет, похоже ли значение на массив ID.

**Параметры:**
- `$value` (mixed) — Значение для проверки.

**Возвращает:**
- `bool` — `true`, если значение — ассоциативный массив с более чем одним элементом.

### `mapIdsToUrls(array $ids): array`

**Приватный метод**, преобразует массив ID в массив URL, используя кэш.

**Параметры:**
- `$ids` (array) — Массив ID.

**Возвращает:**
- `array` — Массив URL (фильтрует `null` значения).

## Особенности реализации

1. **Ленивая загрузка**: URL загружаются только при первом вызове `resolve()`.
2. **Один запрос к БД**: Все URL загружаются одним `SELECT` запросом через `Attachment::whereIn('id', $ids)`.
3. **Типизация URL**: Все URL приводятся к строковому типу через `(string)$url`.
4. **Обработка вложенных массивов**: Рекурсивно обрабатывает любую вложенность, сохраняя структуру данных.

## Зависимости

- `Orchid\Attachment\Models\Attachment` — модель вложений Orchid.
- `Illuminate\Support\Arr` — вспомогательный класс для проверки ассоциативных массивов.

## Использование в проекте

`AttachmentUrlResolver` используется в следующих сервисах и местах:

- **DemoJsonService**: Для обработки данных из JSON-файла `resources/views/data/data.json`.
- **Любые контроллеры и screen-классы**: При необходимости отображения вложений в публичном интерфейсе.

**Важно**: Сервис не регистрируется в `AppServiceProvider` — он автоматически разрешается через Laravel's autowiring.
