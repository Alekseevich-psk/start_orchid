<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Template extends Model
{
    use HasFactory, AsSource, Filterable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'path',
        'icon',
    ];

    /**
     * Настройка фильтров (опционально).
     */
    protected $allowedFilters = [
  
    ];

    /**
     * Поля для сортировки.
     */
    protected $allowedSorts = [
        'title',
        'id',
        'path',
        'created_at',
        'updated_at',
    ];
}
