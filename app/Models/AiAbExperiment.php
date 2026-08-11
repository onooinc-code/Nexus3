<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAbExperiment extends BaseModel
{
    protected $table = 'ai_ab_experiments';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'intent_name',
        'model_a_id',
        'model_b_id',
        'weight_a',
        'weight_b',
        'goal_metric',
        'is_active',
    ];

    protected $casts = [
        'weight_a' => 'integer',
        'weight_b' => 'integer',
        'is_active' => 'boolean',
    ];

    public function modelA(): BelongsTo
    {
        return $this->belongsTo(AIModel::class, 'model_a_id');
    }

    public function modelB(): BelongsTo
    {
        return $this->belongsTo(AIModel::class, 'model_b_id');
    }
}
