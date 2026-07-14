<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'task_type',
        'title_template',
        'description_template',
        'payload_template',
        'expected_variables',
        'default_priority',
        'agent_type',
        'is_active',
    ];

    protected $casts = [
        'payload_template' => 'json',
        'expected_variables' => 'json',
        'is_active' => 'boolean',
        'default_priority' => 'integer',
    ];
}
