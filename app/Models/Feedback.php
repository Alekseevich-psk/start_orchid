<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Feedback extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $fillable = [
        'type',
        'source',
        'form_id',
        'name',
        'email',
        'phone',
        'message',
        'fields',
        'meta',
        'user_id',
        'ip_address',
        'user_agent',
        'referral',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'status',
        'is_read',
        'sent_at',
        'processed_at',
        'expires_at',
    ];

    protected $casts = [
        'fields' => 'array',
        'meta' => 'array',
        'is_read' => 'boolean',
        'sent_at' => 'datetime',
        'processed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}