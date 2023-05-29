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
 *         @OA\Property(property="product", type="string", example="new-brand"),
 *         @OA\Property(property="created_at", type="string", example="2022-08-01 08:24:56"),
 *         @OA\Property(property="id", ref="#/components/schemas/ProductResponse")
 *     }
 * )
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
