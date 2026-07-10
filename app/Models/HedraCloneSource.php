<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HedraCloneSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_type',
        'content',
        'confidence',
        'sensitivity',
        'freshness_score',
        'visibility_scope',
        'validation_status',
        'provenance',
        'is_archived',
    ];

    protected $casts = [
        'confidence' => 'decimal:4',
        'freshness_score' => 'decimal:4',
        'is_archived' => 'boolean',
    ];

    public function scopeActive(Builder $query)
    {
        return $query->where('is_archived', false);
    }
}
