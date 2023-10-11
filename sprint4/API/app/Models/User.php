<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

/** @OA\Schema(
 *     schema="UserRequest",
 *     type="object",
 *     title="UserRequest",
 *     properties={
 *         @OA\Property(property="first_name", type="string", example="John"),
 *         @OA\Property(property="last_name", type="string", example="Doe"),
 *         @OA\Property(property="address", type="string", example="Street 1"),
 *         @OA\Property(property="city", type="string", example="City"),
 *         @OA\Property(property="state", type="string", example="State"),
 *         @OA\Property(property="country", type="string", example="Country"),
 *         @OA\Property(property="postcode", type="string", example="1234AA"),
 *         @OA\Property(property="phone", type="string", example="0987654321"),
 *         @OA\Property(property="dob", type="string", example="1970-01-01"),
 *         @OA\Property(property="email", type="string", example="john@doe.example"),
 *         @OA\Property(property="password", type="string", example="super-secret")
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="UserResponse",
 *     type="object",
 *     title="UserResponse",
 *     properties={
 *         @OA\Property(property="first_name", type="string", example="John"),
 *         @OA\Property(property="last_name", type="string", example="Doe"),
 *         @OA\Property(property="address", type="string", example="Street 1"),
 *         @OA\Property(property="city", type="string", example="City"),
 *         @OA\Property(property="state", type="string", example="State"),
 *         @OA\Property(property="country", type="string", example="Country"),
 *         @OA\Property(property="postcode", type="string", example="1234AA"),
 *         @OA\Property(property="phone", type="string", example="0987654321"),
 *         @OA\Property(property="dob", type="string", example="1970-01-01"),
 *         @OA\Property(property="email", type="string", example="john@doe.example"),
 *         @OA\Property(property="id", type="integer")
 *     }
 * )
 */
class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['first_name', 'last_name', 'address', 'city', 'state', 'country', 'postcode', 'phone', 'dob', 'email', 'password', 'role'];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['updated_at', 'password', 'role'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = array(
        'created_at' => 'datetime:Y-m-d H:i:s'
    );

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return ['role' => $this->role];
    }
}
