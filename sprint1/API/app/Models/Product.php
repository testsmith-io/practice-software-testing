<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @OA\Schema(
 *     schema="ProductRequest",
 *     type="object",
 *     title="ProductRequest",
 *     required={"name","price","category_id","brand_id","product_image_id"},
 *     properties={
 *         @OA\Property(property="name", type="string", maxLength=120),
 *         @OA\Property(property="description", type="string", maxLength=1250, nullable=true),
 *         @OA\Property(property="price", type="number", example=1.99),
 *         @OA\Property(property="category_id", type="integer", example=1),
 *         @OA\Property(property="brand_id", type="integer", example=1),
 *         @OA\Property(property="product_image_id", type="integer", example=1),
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ProductResponse",
 *     type="object",
 *     title="ProductResponse",
 *     properties={
 *         @OA\Property(property="id", type="integer", example=1, readOnly=true),
 *         @OA\Property(property="name", type="string", example="new brand"),
 *         @OA\Property(property="description", type="string", example="Lorum ipsum"),
 *         @OA\Property(property="price", type="number", example=9.99),
 *         @OA\Property(property="brand", ref="#/components/schemas/BrandResponse"),
 *         @OA\Property(property="category", ref="#/components/schemas/CategoryResponse"),
 *         @OA\Property(property="product_image", ref="#/components/schemas/ImageResponse", nullable=true)
 *     }
 * )
 */
class Product extends BaseModel
{
    use HasFactory;

    protected $table = 'products';
    protected $fillable = ['name', 'description', 'category_id', 'brand_id', 'price', 'product_image_id'];
    protected $hidden = ['brand_id', 'category_id', 'product_image_id', 'created_at', 'updated_at'];

    protected $casts = array(
        "price" => "double"
    );

    public function product_image(): BelongsTo
    {
        return $this->belongsTo('App\Models\ProductImage');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo('App\Models\Category');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo('App\Models\Brand');
    }
}
