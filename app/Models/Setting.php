<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Setting extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $fillable = ['title','key', 'value', 'group', 'type'];

    // Автоматическое приведение типов для value
    protected $casts = [
        'value' => 'json',
    ];

    // Разрешённые фильтры
    protected $allowedFilters = [
        'key',
        'group',
        'title',
    ];

    // Поля для сортировки
    protected $allowedSorts = [
        'key',
        'group',
        'title',
        'created_at',
    ];

    /**
     * Статический метод для получения одной настройки
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = self::where('key', $key)->first();
        return $setting?->value ?? $default;
    }

    /**
     * Статический метод для установки значения настройки
     *
     * @param string $key
     * @param mixed $value
     * @return Setting|null
     */
    public static function set(string $key, mixed $value): ?self
    {
        $setting = self::firstOrCreate(['key' => $key]);
        $setting->value = $value;
        $setting->save();
        return $setting;
    }
}
