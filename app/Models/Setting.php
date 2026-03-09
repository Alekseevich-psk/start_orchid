<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'type'];

    // Пример: автоматическое приведение типов
    protected $casts = [
        'value' => 'json',
    ];

    // Скоуп: только булевы значения
    public function scopeBoolean($query)
    {
        return $query->where('type', 'boolean');
    }

    // Скоуп: по группе
    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
