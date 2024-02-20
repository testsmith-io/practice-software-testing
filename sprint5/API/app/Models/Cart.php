<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @OA\Schema(
 *     schema="CartItemResponse",
 *     type="object",
 *     title="CartResponse",
 *     properties={
 *       @OA\Property(property="cart_id", type="string")
 *     }
 * )
 */
class Cart extends BaseModel
{
    use HasFactory, HasUlids;
    protected $table = 'carts';
    protected $fillable = ['lat'];

    protected $casts = array(
        "additional_discount_percentage" => "double"
    );
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
