<?php

use App\Models\Page;

if (!function_exists('page_url')) {
    /**
     * Получить URL страницы по ID
     */
    function page_url($id): ?string
    {
        if (!$id) {
            return null;
        }

        $cacheKey = "page.url.{$id}";

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($id) {
            $page = Page::find($id);
            if (!$page || !$page->is_published) {
                return null;
            }

            return '/' . ltrim($page->slug, '/');
        });
    }
}

if (!function_exists('setting')) {
    /**
     * Получить значение настройки по ключу
     *
     * @param string|null $key Ключ настройки или null для получения сервиса
     * @param mixed $default Значение по умолчанию
     * @return mixed|\App\Services\SettingService
     */
    function setting(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return app(\App\Services\SettingService::class);
        }

        return app(\App\Services\SettingService::class)->get($key, $default);
    }
}

if (!function_exists('settings')) {
    /**
     * Получить все настройки
     *
     * @return array<string, mixed>
     */
    function settings(): array
    {
        return app(\App\Services\SettingService::class)->getAll();
    }
}

if (!function_exists('setting_bool')) {
    /**
     * Получить настройку как boolean
     *
     * @param string $key
     * @param bool $default
     * @return bool
     */
    function setting_bool(string $key, bool $default = false): bool
    {
        return app(\App\Services\SettingService::class)->getBool($key, $default);
    }
}

if (!function_exists('setting_int')) {
    /**
     * Получить настройку как integer
     *
     * @param string $key
     * @param int $default
     * @return int
     */
    function setting_int(string $key, int $default = 0): int
    {
        return app(\App\Services\SettingService::class)->getInt($key, $default);
    }
}

if (!function_exists('setting_array')) {
    /**
     * Получить настройку как array
     *
     * @param string $key
     * @param array $default
     * @return array
     */
    function setting_array(string $key, array $default = []): array
    {
        return app(\App\Services\SettingService::class)->getArray($key, $default);
    }
}

if (!function_exists('setting_group')) {
    /**
     * Получить настройки по группе
     *
     * @param string $group
     * @return array<string, mixed>
     */
    function setting_group(string $group): array
    {
        return app(\App\Services\SettingService::class)->getByGroup($group);
    }
}
