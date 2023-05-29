<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @OA\Schema(
 *     schema="ContactRequest",
 *     type="object",
 *     title="ContactRequest",
 *     required={"name"},
 *     properties={
 *         @OA\Property(property="first_name", type="string", example="John", description=""),
 *         @OA\Property(property="last_name", type="string", example="Doe", description=""),
 *         @OA\Property(property="email", type="string", example="john@doe.example", description=""),
 *         @OA\Property(property="subject", type="string", example="website", description=""),
 *         @OA\Property(property="message", type="string", example="Something is wrong with the website.", description="")
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ContactResponse",
 *     type="object",
 *     title="ContactResponse",
 *     properties={
 *         @OA\Property(property="name", type="string", example="John Doe", description=""),
 *         @OA\Property(property="email", type="string", example="john@doe.example", description=""),
 *         @OA\Property(property="subject", type="string", example="website", description=""),
 *         @OA\Property(property="message", type="string", example="Something is wrong with the website.", description=""),
 *         @OA\Property(property="status", type="string", example="NEW", description=""),
 *         @OA\Property(property="id", type="integer", example="1", description=""),
 *         @OA\Property(property="created_at", type="string", example="2022-08-01 08:24:56")
 *     }
 * )
 */
class ContactRequests extends BaseModel
{
    use HasFactory;

    protected $hidden = ['updated_at'];
    protected $table = 'contact_requests';
    protected $fillable = ['parent_id', 'user_id', 'name', 'email', 'subject', 'message', 'status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ContactRequestReply::class, 'message_id');
    }

}
