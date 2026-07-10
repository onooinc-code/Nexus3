<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactReplyRule extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'contact_id',
        'rule',
        'is_active',
        'source_type',
        'source_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
