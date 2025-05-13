<?php

namespace App\Models;

use App\Observers\CachedAuthUserObserver;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Exceptions\JWTException;

/** @OA\Schema(
 *     schema="UserRequest",
 *     type="object",
 *     title="UserRequest",
 *     required={"first_name", "last_name", "email", "password"},
 *     properties={
 *         @OA\Property(property="first_name", type="string", maxLength=40, example="John"),
 *         @OA\Property(property="last_name", type="string", maxLength=20, example="Doe"),
 *         @OA\Property(
 *              property="address",
 *              type="object",
 *              @OA\Property(property="street", type="string", maxLength=70, example="Street 1"),
 *              @OA\Property(property="city", type="string", maxLength=40, example="City"),
 *              @OA\Property(property="state", type="string", maxLength=40, example="State"),
 *              @OA\Property(property="country", type="string", maxLength=40, example="Country"),
 *              @OA\Property(property="postal_code", type="string", maxLength=10, example="1234AA")
 *         ),
 *         @OA\Property(property="phone", type="string", maxLength=24, example="0987654321"),
 *         @OA\Property(
 *             property="dob",
 *             type="string",
 *             format="date",
 *             example="1970-01-01",
 *             description="Must be a valid date between 18 and 75 years ago"
 *         ),
 *         @OA\Property(
 *             property="password",
 *             type="string",
 *             format="password",
 *             minLength=8,
 *             example="SuperSecure@123",
 *             description="Must include uppercase, lowercase, number, and symbol"
 *         ),
 *         @OA\Property(
 *             property="email",
 *             type="string",
 *             format="email",
 *             maxLength=256,
 *             example="john@doe.example"
 *         )
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
 *         @OA\Property(
 *              property="address",
 *              type="object",
 *              @OA\Property(property="street", type="string", example="Street 1"),
 *              @OA\Property(property="city", type="string", example="City"),
 *              @OA\Property(property="state", type="string", example="State"),
 *              @OA\Property(property="country", type="string", example="Country"),
 *              @OA\Property(property="postal_code", type="string", example="1234AA")
 *         ),
 *         @OA\Property(property="phone", type="string", example="0987654321"),
 *         @OA\Property(property="dob", type="string", example="1970-01-01"),
 *         @OA\Property(property="email", type="string", example="john@doe.example"),
 *         @OA\Property(property="id", type="string"),
 *         @OA\Property(property="provider", type="string"),
 *         @OA\Property(property="totp_enabled", type="boolean"),
 *         @OA\Property(property="enabled", type="boolean"),
 *         @OA\Property(property="failed_login_attempts", nullable=true, type="integer"),
 *         @OA\Property(property="created_at", type="string", example="2022-08-01 08:24:56"),
 *     }
 * )
 */
#[ObservedBy([CachedAuthUserObserver::class])]
class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasUlids;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['first_name', 'last_name', 'street', 'city', 'state', 'country', 'postal_code', 'phone', 'dob', 'email', 'password', 'role', 'enabled', 'failed_login_attempts', 'totp_secret', 'totp_verified_at', 'totp_enabled'];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['enabled', 'failed_login_attempts', 'updated_at', 'password', 'role', 'uid', 'totp_secret', 'totp_verified_at', 'street', 'city', 'state', 'country', 'postal_code'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = array(
        'created_at' => 'datetime:Y-m-d H:i:s',
        'enabled' => 'boolean',
        'totp_enabled' => 'boolean',
    );

//    protected $appends = ['admin_details'];
    protected $appends = ['address'];

    public function getAddressAttribute()
    {
        return [
            'street' => $this->street,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code, // Renamed for API consistency
        ];
    }


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
                $array['role'] = $this->role;
                $array['failed_login_attempts'] = $this->failed_login_attempts;
            }
        } catch (JWTException $e) {
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
