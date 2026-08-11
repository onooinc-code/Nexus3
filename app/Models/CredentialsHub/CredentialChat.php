<?php

namespace App\Models\CredentialsHub;

use Illuminate\Database\Eloquent\Model;

class CredentialChat extends Model
{
    protected $table = 'credential_chats';

    protected $fillable = [
        'role',
        'content',
    ];
}
