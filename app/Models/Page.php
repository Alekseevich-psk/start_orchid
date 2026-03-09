<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Page extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'excerpt',
        'image',
        'content',
        'blocks',
        'type',
        'is_category',
        'parent',
        'slug',
        'is_published',
        'template_id',
        'in_menu',
        'menu_order',
        'alias',
        'published_at',
        'unpublished_at',
        'allowed_roles',
    ];

    protected $casts = [
        'blocks'         => 'array',
        'allowed_roles'  => 'array',
        'published_at'   => 'datetime',
        'unpublished_at' => 'datetime',
        'is_published'   => 'boolean',
        'is_category'    => 'boolean',
        'in_menu'        => 'boolean',
    ];

    /**
     * Настройка фильтров (опционально).
     */
    protected $allowedFilters = [
        // 'slug',
    ];

    /**
     * Поля для сортировки.
     */
    protected $allowedSorts = [
        'title',
        'id',
        'slug',
        'created_at',
        'updated_at',
    ];
}
