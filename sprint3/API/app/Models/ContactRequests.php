<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactRequests extends BaseModel
{
    protected $table = 'contact_requests';
    protected $fillable = ['user_id', 'name', 'email', 'subject', 'message', 'status'];

    public function replies(): HasMany
    {
        return $this->hasMany(ContactRequestReply::class, 'message_id');
    }
}
