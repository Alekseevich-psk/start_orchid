<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    /**
     * Получить все настройки в виде ассоциативного массива (ключ => значение)
     * Кэшируется на 1 час
     *
     * @return array<string, mixed>
     */
    public function getAll(): array
    {
        return Cache::remember('settings.all', 3600, function () {
            return Setting::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Получить настройку по ключу
     *
     * @param string $key Ключ настройки
     * @param mixed $default Значение по умолчанию, если не найдено
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("settings.{$key}", 3600, function () use ($key) {
            $setting = Setting::where('key', $key)->first();
            return $setting?->value;
        }) ?? $default;
    }

    /**
     * Получить несколько настроек по массиву ключей
     *
     * @param array<string> $keys
     * @return array<string, mixed>
     */
    public function getMultiple(array $keys): array
    {
        $results = [];
        
        foreach ($keys as $key) {
            $results[$key] = $this->get($key);
        }
        
        return $results;
    }

    /**
     * Получить настройку с приведением к boolean
     *
     * @param string $key Ключ настройки
     * @param bool $default Значение по умолчанию
     * @return bool
     */
    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->get($key, $default);
        
        // Приведение к boolean
        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'on', 'yes']);
        }
        
        return (bool) $value;
    }

    /**
     * Получить настройку с приведением к integer
     *
     * @param string $key Ключ настройки
     * @param int $default Значение по умолчанию
     * @return int
     */
    public function getInt(string $key, int $default = 0): int
    {
        $value = $this->get($key, $default);
        return (int) $value;
    }

    /**
     * Получить настройку с приведением к float
     *
     * @param string $key Ключ настройки
     * @param float $default Значение по умолчанию
     * @return float
     */
    public function getFloat(string $key, float $default = 0.0): float
    {
        $value = $this->get($key, $default);
        return (float) $value;
    }

    /**
     * Получить настройку с приведением к array (если JSON)
     *
     * @param string $key Ключ настройки
     * @param array $default Значение по умолчанию
     * @return array
     */
    public function getArray(string $key, array $default = []): array
    {
        $value = $this->get($key, $default);
        
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return $decoded !== null ? $decoded : $default;
        }
        
        return is_array($value) ? $value : $default;
    }

    /**
     * Получить настройки по группе
     *
     * @param string $group Название группы
     * @return array<string, mixed>
     */
    public function getByGroup(string $group): array
    {
        return Cache::remember("settings.group.{$group}", 3600, function () use ($group) {
            return Setting::where('group', $group)
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /**
     * Обновить настройку по ключу
     *
     * @param string $key Ключ настройки
     * @param mixed $value Новое значение
     * @return Setting|null
     */
    public function update(string $key, mixed $value): ?Setting
    {
        $setting = Setting::firstOrCreate(['key' => $key]);
        $setting->value = $value;
        $setting->save();

        // Очищаем кэш для этой настройки
        $this->flushCacheFor($key);

        return $setting;
    }

    /**
     * Обновить несколько настроек сразу
     *
     * @param array<string, mixed> $settings
     * @return void
     */
    public function updateMultiple(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->update($key, $value);
        }
    }

    /**
     * Очистить кэш для конкретной настройки
     *
     * @param string|null $key Ключ или null для очистки всего кэша настроек
     * @return void
     */
    public function flushCache(?string $key = null): void
    {
        if ($key) {
            Cache::forget("settings.{$key}");
        } else {
            Cache::forget('settings.all');
            // Очищаем все настройки по префиксу
            $cache = app('cache.store');
            if (method_exists($cache, 'tags')) {
                $cache->tags('settings')->flush();
            }
        }
    }

    /**
     * Очистить кэш для конкретной настройки (alias)
     *
     * @param string $key
     * @return void
     */
    private function flushCacheFor(string $key): void
    {
        $this->flushCache($key);
    }

    /**
     * Проверить, существует ли настройка
     *
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        return Setting::where('key', $key)->exists();
    }

    /**
     * Получить все доступные ключи настроек
     *
     * @return array<string>
     */
    public function getKeys(): array
    {
        return Setting::pluck('key')->toArray();
    }

    /**
     * Получить все группы настроек
     *
     * @return array<string>
     */
    public function getGroups(): array
    {
        return Setting::distinct()->pluck('group')->toArray();
    }

    /**
     * Получить описание настройки (title)
     *
     * @param string $key
     * @return string|null
     */
    public function getTitle(string $key): ?string
    {
        $setting = Setting::where('key', $key)->first();
        return $setting?->title;
    }

    /**
     * Получить тип настройки (text, textarea, boolean и т.д.)
     *
     * @param string $key
     * @return string|null
     */
    public function getType(string $key): ?string
    {
        $setting = Setting::where('key', $key)->first();
        return $setting?->type;
    }

    /**
     * Получить группу настройки
     *
     * @param string $key
     * @return string|null
     */
    public function getGroup(string $key): ?string
    {
        $setting = Setting::where('key', $key)->first();
        return $setting?->group;
    }
}
