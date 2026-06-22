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

    // Пример: автоматическое приведение типов
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

}
