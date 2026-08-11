<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HermesSession extends Model
{
    use HasFactory;

    protected $table = 'hermes_sessions';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'title',
        'source',
        'model',
        'started_at',
        'ended_at',
        'end_reason',
        'message_count',
        'tool_call_count',
        'input_tokens',
        'output_tokens',
        'preview',
        'last_active',
        'pinned',
        'archived',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'last_active' => 'datetime',
        'pinned' => 'boolean',
        'archived' => 'boolean',
    ];

    public function messages()
    {
        return $this->hasMany(HermesMessage::class, 'hermes_session_id', 'id');
    }
}
