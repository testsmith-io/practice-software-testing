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
 *         @OA\Property(property="name", type="string", example="John Doe", description="If set, Authorization header is required"),
 *         @OA\Property(property="email", type="string", example="john@doe.example", description=""),
 *         @OA\Property(property="subject", type="string", example="website", description=""),
 *         @OA\Property(property="message", type="string", example="Something is wrong with the website.", description="")
 *     }
 * )
 *
 * @OA\Schema(
 *      schema="ContactRequestAuthenticated",
 *      type="object",
 *      title="ContactRequestAuthenticated",
 *      required={"name"},
 *      properties={
 *          @OA\Property(property="name", type="string", example="John Doe", description="If set, Authorization header is required"),
 *          @OA\Property(property="subject", type="string", example="website", description=""),
 *          @OA\Property(property="message", type="string", example="Something is wrong with the website.", description="")
 *      }
 *  )
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
 *
 * @OA\Schema(
 *      schema="ContactResponseAuthenticated",
 *      type="object",
 *      title="ContactResponseAuthenticated",
 *      properties={
 *          @OA\Property(property="user_id", type="integer", example="John Doe", description=""),
 *          @OA\Property(property="name", type="string", example="John Doe", description=""),
 *          @OA\Property(property="email", type="string", example="john@doe.example", description=""),
 *          @OA\Property(property="subject", type="string", example="website", description=""),
 *          @OA\Property(property="message", type="string", example="Something is wrong with the website.", description=""),
 *          @OA\Property(property="status", type="string", example="NEW", description=""),
 *          @OA\Property(property="id", type="integer", example="1", description=""),
 *          @OA\Property(property="created_at", type="string", example="2022-08-01 08:24:56")
 *      }
 *  )
 * @OA\Schema(
 *       schema="ContactResponseFull",
 *       type="object",
 *       title="ContactResponseFull",
 *       description="A detailed contact message response with user and replies",
 *       @OA\Property(property="id", type="integer", example="01jnx2z1a6s8qx9z3hhqy3rdyp"),
 *       @OA\Property(property="user_id", type="integer", example="01JNX24JV5Q3QFDB2ZPTRBMFN8"),
 *       @OA\Property(property="name", type="string", example=null),
 *       @OA\Property(property="email", type="string", example=null),
 *       @OA\Property(property="subject", type="string", example="test-subject"),
 *       @OA\Property(property="message", type="string", example="This is a test contact message."),
 *       @OA\Property(property="status", type="string", example="IN_PROGRESS"),
 *       @OA\Property(property="created_at", type="string", example="2025-03-09 09:14:49"),
 *       @OA\Property(
 *           property="user",
 *           type="object",
 *           ref="#/components/schemas/UserResponse"
 *       ),
 *       @OA\Property(
 *           property="replies",
 *           type="array",
 *           @OA\Items(ref="#/components/schemas/ContactReplyResponse")
 *       )
 *   )
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
