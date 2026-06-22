# DemoJsonService

## Описание

`DemoJsonService` — сервис для загрузки и управления демонстрационными данными из JSON-файла `resources/views/data/data.json`. Он используется для хранения статического контента (тексты, настройки, структуры меню) и интегрируется с `AttachmentUrlResolver` для автоматической замены ID вложений на URL.

Сервис кэширует данные на 1 час, обеспечивая баланс между производительностью и актуальностью контента.

## Основные возможности

- **Загрузка из JSON**: Автоматическая загрузка данных из `resources/views/data/data.json`.
- **Кэширование**: Кэширование на 1 час (`site.data`) для оптимизации производительности.
- **Обработка вложений**: Интеграц��я с `AttachmentUrlResolver` для замены ID на URL.
- **Гибкий доступ**: Метод `get()` для получения значений по ключу в формате `dot-notation` (например, `home.hero.title`).
- **Полный доступ**: Метод `all()` для получения всех данных.

## Примеры использования

### В контроллере

```php
use App\\Services\\DemoJsonService;

class HomeController extends Controller
{
    public function index(DemoJsonService $json)
    {
        // Получить все данные
        $allData = $json->all();

        // Получить конкретное значение
        $heroTitle = $json->get('home.hero.title');
        $heroSubtitle = $json->get('home.hero.subtitle');

        // Получить массив с default-значением
        $features = $json->get('features', []);

        return view('home.index', [
            'heroTitle' => $heroTitle,
            'heroSubtitle' => $heroSubtitle,
            'features' => $features,
        ]);
    }
}
```

### В Blade-шаблоне

```blade
@php
    use App\\Services\\DemoJsonService;
    $json = app(DemoJsonService::class);
@endphp

<header class=\"hero\">
    <h1>{{ $json->get('home.hero.title') }}</h1>
    <p>{{ $json->get('home.hero.subtitle') }}</p>
</header>

<section class=\"features\">
    @foreach ($json->get('features', []) as $feature)
        <div class=\"feature\">
            <h3>{{ $feature['title'] }}</h3>
            <p>{{ $feature['description'] }}</p>
        </div>
    @endforeach
</section>
```

### В Screen-классе Orchid

```php
use App\\Services\\DemoJsonService;

class SettingsScreen extends Screen
{
    public function layout(): array
    {
        $json = app(DemoJsonService::class);

        return [
            Layout::view('platform::settings.demo')
                ->with('demoData', $json->all()),
        ];
    }
}
```

## Методы

### `__construct()`

**Конструктор**, автоматически вызывает `loadData()` при создании экземпляра.

---

### `loadData(): void`

**Приватный метод**, загружает данные из JSON-файла и кэширует их.

**Алгоритм:**
1. Определяет путь к файлу: `base_path('resources/views/data/data.json')`.
2. Пытается получить данные из кэша (`site.data`).
3. Если кэш пуст:
   - Читает файл через `file_get_contents()`.
   - Декодирует JSON через `json_decode()`.
   - Вызывает `AttachmentUrlResolver::resolve()` для замены ID на URL.
   - Сохраняет в кэш на 1 час.
4. Если ошибка JSON → выбрасывает `RuntimeException`.

**Исключения:**
- `RuntimeException` — если JSON некорректен.

---

### `get(string $key, $default = null)`

**Публичный метод**, получает значение по ключу в формате `dot-notation`.

**Параметры:**
- `$key` (string) — Ключ в формате `dot-notation` (например, `home.hero.title`).
- `$default` (mixed) — Значение по умолчанию, если ключ не найден (по умолчанию `null`).

**Возвращает:**
- `mixed` — Значение по ключу или `$default`.

**Примеры:**
```php
$json->get('home.hero.title');           // 'Добро пожаловать'
$json->get('home.hero.image', []);       // [123] или []
$json->get('nonexistent.key', 'default'); // 'default'
```

---

### `all(): array`

**Публичный метод**, возвращает все кэшированные данные.

**Возвращает:**
- `array` — Полный массив данных из JSON.

**Пример:**
```php
$data = $json->all();
// [
//     'home' => ['hero' => ['title' => '...', 'subtitle' => '...']],
//     'features' => [...],
//     'footer' => [...],
// ]
```

## Структура JSON-файла

Пример структуры `resources/views/data/data.json`:

```json
{
  "home": {
    "hero": {
      "title": "Добро пожаловать в нашу CMS",
      "subtitle": "Легко управляйте контентом и структурой сайта",
      "image": [123],
      "button_text": "Начать",
      "button_url": "/about"
    }
  },
  "features": [
    {
      "title": "Гибкая структура",
      "description": "Дерево страниц с неограниченной вложенностью",
      "icon": "folder"
    },
    {
      "title": "Orchid Admin",
      "description": "Современный интерфейс админки",
      "icon": "grid"
    }
  ],
  "footer": {
    "copyright": "© 2026 StartOrchid",
    "links": [
      {
        "title": "О нас",
        "url": "/about"
      },
      {
        "title": "Контакты",
        "url": "/contact"
      }
    ]
  }
}
```

## Особенности реализации

1. **Кэширование**: Данные кэшируются под ключом `site.data` на 1 час, снижая нагрузку на файловую систему.
2. **Обработка вложений**: Автоматическая замена ID вложений (например, `["123"]`) на URL через `AttachmentUrlResolver`.
3. **Безопасность**: Проверка ошибок JSON с подробным сообщением.
4. **Легковесность**: Нет регистрации в `AppServiceProvider` — используется через autowiring.

## Зависимости

- `App\\Services\\AttachmentUrlResolver` — для замены ID вложений на URL.
- `Illuminate\\Support\\Facades\\Cache` — кэширование данных.

## Использование в проекте

`DemoJsonService` применяется в следующих местах:

- **Frontend**: Загрузка статического контента (hero-секции, футеры, списки фич).
- **Admi**n: Отображение демонстрационных данных в настройках.
- **Blade-шаблоны**: Прямой доступ к JSON-данным через helper-функции.

## Рекомендации по использованию

- **Используйте для статики**: JSON подходит для нечасто меняющихся данных (тексты, структура).
- **Для динамики — настройки**: Если данные часто меняются, используйте `SettingService`.
- **Проверяйте структуру**: При изменении JSON-файла убедитесь в корректности ключей (точки, кавычки).
- **Кэш-очистка**: Если данные не обновляются, выполните `php artisan cache:clear` или дождитесь истечения TTL (1 час).