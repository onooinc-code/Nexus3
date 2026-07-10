<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactTopic extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'contact_id',
        'topic',
        'status',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function mentions(): HasMany
    {
        return $this->hasMany(ContactTopicMention::class, 'topic_id');
    }
}
