<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @OA\Schema(
 *     schema="FavoriteRequest",
 *     type="object",
 *     title="FavoriteRequest",
 *     properties={
 *         @OA\Property(property="product_id", type="integer")
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="FavoriteResponse",
 *     type="object",
 *     title="FavoriteResponse",
 *     properties={
 *         @OA\Property(property="product_id", type="integer", example=1234),
 *         @OA\Property(property="user_id", type="integer", example=1234),
 *         @OA\Property(property="id", type="integer", example=1234)
 *     }
 * )
 * @OA\Schema(
 *      schema="FavoriteWithProductResponse",
 *      type="object",
 *      title="FavoriteResponse",
 *      properties={
 *          @OA\Property(property="product_id", type="integer", example=1234),
 *          @OA\Property(property="user_id", type="integer", example=1234),
 *          @OA\Property(property="id", type="integer", example=1234),
 *          @OA\Property(property="product", type="object", ref="#/components/schemas/ProductResponse")
 *      }
 *  )
 */
class Favorite extends BaseModel
{
    use HasFactory;

    protected $table = 'favorites';
    protected $fillable = ['user_id', 'product_id'];

    public function product(): BelongsTo
    {
        return $this->belongsTo('App\Models\Product');
    }
}
