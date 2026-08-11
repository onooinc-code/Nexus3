<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactStance extends BaseModel
{
    use HasFactory;

    protected $table = 'contact_stances';

    protected $fillable = [
        'contact_id',
        'topic',
        'hedra_stance',
        'boundary_rules',
        'past_incidents',
    ];

    protected $casts = [
        'boundary_rules' => 'array',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
