<?php

namespace App\Models\CredentialsHub;

use Illuminate\Database\Eloquent\Model;

class CredentialLog extends Model
{
    protected $table = 'credential_logs';

    protected $fillable = [
        'action',
        'title',
        'details',
        'ip_address',
    ];
}
