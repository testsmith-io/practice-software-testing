<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="ContactReplyResponse",
 *     type="object",
 *     title="ContactReplyResponse",
 *     properties={
 *         @OA\Property(property="message", type="string", example="Reply message", description=""),
 *         @OA\Property(property="id", type="string", example="1", description=""),
 *         @OA\Property(property="created_at", type="string", example="2022-08-01 08:24:56")
 *     }
 * )
 */
class ContactRequestReply extends BaseModel
{
    use HasFactory, HasUlids;

    protected $hidden = ['updated_at', 'message_id', 'user_id'];
    protected $table = 'contact_request_replies';
    protected $fillable = ['user_id', 'message_id', 'message'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
