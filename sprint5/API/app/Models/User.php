<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
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
 *         @OA\Property(property="password", type="string", example="super-secret"),
 *         @OA\Property(property="email", type="string", example="john@doe.example")
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
 *         @OA\Property(property="id", type="string")
 *     }
 * )
 */
class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasUlids;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['first_name', 'last_name', 'address', 'city', 'state', 'country', 'postcode', 'phone', 'dob', 'email', 'password', 'role', 'failed_login_attempts'];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['enabled', 'failed_login_attempts', 'updated_at', 'password', 'role', 'uid'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = array(
        'created_at' => 'datetime:Y-m-d H:i:s'
    );

//    protected $appends = ['admin_details'];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return ['role' => $this->role];
    }

    public function toArray()
    {
        $array = parent::toArray();

        try {
            $role = app('auth')->parseToken()->getPayload()->get('role');
            if ($role == "admin") {
                // Directly add the attributes to the root of the array
                $array['enabled'] = $this->enabled;
                $array['failed_login_attempts'] = $this->failed_login_attempts;
            }
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
        }

        return $array;
    }

//    public function getAdminDetailsAttribute()
//    {
//        try {
//            $role = app('auth')->parseToken()->getPayload()->get('role');
//            if ($role == "admin") {
//                return [
//                    'enabled' => $this->enabled,
//                    'failed_login_attempts' => $this->failed_login_attempts,
//                ];
//            }
//        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
//        }
//
//        return null;
//    }
}
