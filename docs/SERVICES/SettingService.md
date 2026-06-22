# Сервис настроек (Settings Service)

## Описание

Сервис `SettingService` предоставляет удобный и кэшируемый доступ к настройкам конфигурации, хранящимся в базе данных.

## Созданные файлы

1. **`app/Services/SettingService.php`** — основной сервис
2. **Обновление `app/Models/Setting.php`** — добавлены статические методы
3. **Обновление `app/Helpers/PageHelper.php`** — добавлены helper-функции

---

## Использование

### 1. Через helper-функции (рекомендуется)

#### Получить одну настройку:
```php
$siteName = setting('site_name');
$seoDescription = setting('seo_description', 'Значение по умолчанию');
```

#### Получить все настройки:
```php
$allSettings = settings();
```

#### Получить как boolean:
```php
$maintenance = setting_bool('maintenance_mode', false);
$cacheEnabled = setting_bool('cache_enabled', true);
```

#### Получить как integer:
```php
$timeout = setting_int('cache_timeout', 3600);
```

#### Получить как array:
```php
$analyticsIds = setting_array('analytics_ids', []);
```

#### Получить настройки по группе:
```php
$seoSettings = setting_group('seo');
$systemSettings = setting_group('system');
```

### 2. Через сервис (через Dependency Injection)

```php
use App\Services\SettingService;

class MyController extends Controller
{
    public function index(SettingService $settingService)
    {
        // Получить все настройки
        $all = $settingService->getAll();
        
        // Получить одну настройку
        $siteName = $settingService->get('site_name');
        
        // Получить несколько настроек
        $keys = ['site_name', 'seo_description', 'maintenance_mode'];
        $settings = $settingService->getMultiple($keys);
        
        // Обновить настройку
        $settingService->update('site_name', 'Новое название');
    }
}
```

### 3. Через facade (если зарегистрировать)

Можно зарегистрировать facade в `config/app.php`:

```php
'aliases' => [
    // ...
    'Setting' => App\Facades\Setting::class,
]
```

---

## Кэширование

Все методы кэшируют результаты на **1 час (3600 секунд)**.

### Очистка кэша

Кэш автоматически очищается при обновлении настройки через метод `update()`.

Для ручной очистки:

```php
use App\Services\SettingService;

$settingService = app(SettingService::class);

// Очистить конкретную настройку
$settingService->flushCache('site_name');

// Очистить все настройки
$settingService->flushCache();
```

---

## Структура таблицы `settings`

| Поле | Тип | Описание |
|------|-----|----------|
| `id` | bigint | Идентификатор |
| `key` | string | Уникальный ключ настройки |
| `value` | json | Значение (автоматически сериализуется) |
| `group` | string | Группа настройки (general, seo, system, analytics) |
| `type` | string | Тип поля (text, textarea, boolean, select) |
| `title` | string | Человекочитаемое название |
| `created_at` | timestamp | Дата создания |
| `updated_at` | timestamp | Дата обновления |

---

## Примеры

### В контроллере:
```php
public function index(SettingService $settingService)
{
    $siteName = $settingService->get('site_name');
    $analytics = $settingService->getArray('analytics_ids', []);
    $maintenance = $settingService->getBool('maintenance_mode');
    
    return view('home', compact('siteName', 'analytics', 'maintenance'));
}
```

### В Blade-шаблоне:
```blade
<title>{{ setting('site_name') }} - {{ setting('seo_description') }}</title>

@if(setting_bool('maintenance_mode'))
    <div class="alert">Сайт на техническом обслуживании</div>
@endif

<script>
    var analyticsId = "{{ setting('google_analytics_id') }}";
</script>
```

### В middleware:
```php
public function handle($request, Closure $next)
{
    if (setting_bool('maintenance_mode')) {
        return response()->view('errors.maintenance');
    }
    
    return $next($request);
}
```

### Обновление из админки:
```php
// В Orchid Screen или контроллере
$settingService->update('site_name', $request->get('site_name'));
$settingService->update('maintenance_mode', $request->get('maintenance_mode'));
```

---

## Примечания

- Все настройки автоматически кэшируются
- Кэш очищается автоматически при обновлении настройки
- Методы `getBool()`, `getInt()`, `getArray()` автоматически приводят типы
- Если настройка не найдена, возвращается значение по умолчанию (если указано)
- Используйте helper-функции для простых случаев, сервис для сложной логики
