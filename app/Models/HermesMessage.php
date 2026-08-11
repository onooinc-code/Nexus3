<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HermesMessage extends Model
{
    use HasFactory;

    protected $table = 'hermes_messages';

    protected $fillable = [
        'hermes_session_id',
        'role',
        'content',
        'raw_payload',
        'timestamp',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'timestamp' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(HermesSession::class, 'hermes_session_id', 'id');
    }
}
