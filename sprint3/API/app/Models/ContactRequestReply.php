<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactRequestReply extends BaseModel
{
    protected $table = 'contact_request_replies';
    protected $fillable = ['user_id', 'message_id', 'message'];

    public function contactRequest(): BelongsTo
    {
        return $this->belongsTo(ContactRequests::class, 'message_id');
    }
}
