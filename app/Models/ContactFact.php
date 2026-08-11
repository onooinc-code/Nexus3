<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactFact extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'contact_facts';

    protected $fillable = [
        'contact_id',
        'category',
        'fact_key',
        'fact_value',
        'confidence',
        'source',
        'source_ref',
        'last_verified_at',
    ];

    protected $casts = [
        'confidence' => 'float',
        'last_verified_at' => 'datetime',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
