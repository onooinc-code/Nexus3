<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageHistoryIndex extends BaseModel
{
    use HasFactory;

    protected $table = 'message_history_index';

    protected $fillable = [
        'contact_id',
        'platform',
        'sender_type',
        'message_text',
        'timestamp',
        'sentiment',
        'topics',
        'source_id',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'topics' => 'array',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
