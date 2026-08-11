<?php

namespace App\Models;

class AIApiKey extends BaseModel
{
    protected $table = 'ai_api_keys';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'provider_id',
        'key_hash',
        'name',
        'is_active',
        'is_default',
        'status',
        'expires_at',
        'last_rotated_at',
        'workspace_id',
        'last_used_at',
        'cooldown_until',
        'error_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'expires_at' => 'datetime',
        'last_rotated_at' => 'datetime',
        'last_used_at' => 'datetime',
        'cooldown_until' => 'datetime',
        'error_count' => 'integer',
    ];

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function provider()
    {
        return $this->belongsTo(AIProvider::class, 'provider_id');
    }
}
