<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Panel extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'title',
        'zone',
        'type',
        'order',
        'is_open',
        'settings',
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'settings' => 'array',
        'order' => 'integer',
    ];
}
