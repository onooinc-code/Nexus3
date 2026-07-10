<?php

namespace App\Models\PeopleConnect;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeopleConnectReplyModeOverride extends Model
{
    protected $table = 'peopleconnect_reply_mode_overrides';

    protected $fillable = ['contact_id', 'reply_mode', 'set_by', 'reason'];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
