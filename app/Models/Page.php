<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use Orchid\Attachment\Attachable;
use Orchid\Attachment\Models\Attachment;

class Page extends Model
{
    use HasFactory, AsSource, Filterable, Attachable;

    protected $appends = ['url'];

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
        'parent_id',
        'slug',
        'ico',
        'is_published',
        'template_id',
        'in_menu',
        'menu_order',
        'alias',
        'published_at',
        'unpublished_at',
        'allowed_roles',
        'in_slug_path',
        'template_child_id',
    ];

    protected $casts = [
        'blocks'         => 'array',
        'allowed_roles'  => 'array',
        'published_at'   => 'datetime',
        'unpublished_at' => 'datetime',
        'is_published'   => 'boolean',
        'is_category'    => 'boolean',
        'in_menu'        => 'boolean',
        'in_slug_path'   => 'boolean',
        'indexed'        => 'boolean',
    ];

    /**
     * Настройка фильтров (опционально).
     */
    protected $allowedFilters = [
        // 'slug',
    ];

    /**
     * Scope a query to only include pages in menu.
     */
    public function scopeInMenu($query)
    {
        return $query->where('in_menu', true);
    }

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

    // Значение по умолчанию для indexed
    protected $attributes = [
        'indexed' => true,
        'in_menu' => true,
        'in_slug_path' => true,
        'is_published' => true,
        'is_category' => false,
    ];

    public function getUrlAttribute(): string
    {
        return '/' . ltrim($this->slug ?? $this->alias, '/');
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function parent()
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Page::class, 'parent_id');
    }

    public function imageAttachment()
    {
        return $this->hasOne(Attachment::class, 'id', 'image')
            ->withDefault();
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('indexed', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('unpublished_at')
                    ->orWhere('unpublished_at', '>=', now());
            });
    }
}
