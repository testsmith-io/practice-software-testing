<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @OA\Schema(
 *     schema="CartResponse",
 *     type="object",
 *     title="CartResponse",
 *     properties={
 *       @OA\Property(property="cart_id", type="string")
 *     }
 * )
 */
class CartItem extends BaseModel
{
    use HasFactory, HasUlids;
    protected $table = 'cart_items';
    protected $fillable = ['cart_id', 'product_id', 'quantity'];

    protected $casts = array(
        "discount_percentage" => "double"
    );

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
