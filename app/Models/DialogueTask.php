<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DialogueTask extends BaseModel
{
    use HasFactory;

    protected $table = 'dialogue_tasks';

    protected $primaryKey = 'task_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'task_id',
        'contact_id',
        'goal_type',
        'target_outcome',
        'current_state',
        'status',
        'last_message_at',
        'checkpoint_data',
        'hedra_approval_reason',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'checkpoint_data' => 'array',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
