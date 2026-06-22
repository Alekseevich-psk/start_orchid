<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Field extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $table = 'fields';

    protected $fillable = [
        'field_id',
        'title',
        'type',
        'options',
        'model_type',
        'model_id',
    ];

    /**
     * Настройка фильтров (опционально).
     */
    protected $allowedFilters = [];

    /**
     * Поля для сортировки.
     */
    protected $allowedSorts = [
        'title',
        'id',
        'created_at',
    ];

    // Если у вас есть связь с Page или Template через model_type и model_id
    // можно добавить morphTo, если нужно
}
