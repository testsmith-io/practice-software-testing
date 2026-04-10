<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="ProductSpecRequest",
 *     type="object",
 *     title="ProductSpecRequest",
 *     required={"product_id", "spec_name", "spec_value"},
 *     properties={
 *         @OA\Property(property="product_id", type="string"),
 *         @OA\Property(property="spec_name", type="string", example="Weight"),
 *         @OA\Property(property="spec_value", type="string", example="1.5"),
 *         @OA\Property(property="spec_unit", type="string", example="kg", nullable=true)
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ProductSpecResponse",
 *     type="object",
 *     title="ProductSpecResponse",
 *     properties={
 *         @OA\Property(property="id", type="string"),
 *         @OA\Property(property="product_id", type="string"),
 *         @OA\Property(property="spec_name", type="string"),
 *         @OA\Property(property="spec_value", type="string"),
 *         @OA\Property(property="spec_unit", type="string", nullable=true)
 *     }
 * )
 */
class ProductSpec extends BaseModel
{
    use HasFactory, HasUlids;

    protected $table = 'product_specs';
    protected $fillable = ['product_id', 'spec_name', 'spec_value', 'spec_unit'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
