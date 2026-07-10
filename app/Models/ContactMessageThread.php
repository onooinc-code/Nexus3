<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactMessageThread extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'contact_id',
        'source',
        'source_thread_id',
        'channel',
        'name',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ContactMessage::class, 'thread_id');
    }
}
