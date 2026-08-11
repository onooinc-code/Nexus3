<?php

namespace App\Models\CredentialsHub;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credential extends Model
{
    use HasFactory;

    protected $table = 'credentials';

    protected $fillable = [
        'category',
        'title',
        'subtitle',
        'icon',
        'icon_bg',
        'test_status',
        'test_code',
        'fields',
        'last_tested_at',
    ];

    protected $casts = [
        'fields' => 'array',
        'last_tested_at' => 'datetime',
    ];
}
