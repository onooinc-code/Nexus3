<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class AIProvider extends BaseModel
{
    public $resolved_api_key;

    protected $table = 'ai_providers';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'base_url',
        'models_fetch_endpoint',
        'generate_endpoint',
        'test_endpoint',
        'auth_header_format',
        'payload_format',
        'is_active',
        'last_synced_at',
        'notes',
        'tags',
        'sort_order',
        'is_favorite',
        'auto_sync_interval',
        'circuit_breaker_threshold',
        'request_timeout_ms',
        'max_retries',
        'monthly_budget_cap',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
        'tags' => 'array',
        'is_favorite' => 'boolean',
        'monthly_budget_cap' => 'decimal:4',
    ];

    public function getApiKeyAttribute()
    {
        return $this->resolved_api_key;
    }

    public function models(): HasMany
    {
        return $this->hasMany(AIModel::class, 'provider_id');
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(AIApiKey::class, 'provider_id');
    }

    public function healthMetrics(): HasMany
    {
        return $this->hasMany(\App\Models\ProviderHealthMetric::class, 'provider_id');
    }
}
