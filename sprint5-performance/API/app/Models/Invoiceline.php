<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="InvoiceLineResponse",
 *     type="object",
 *     title="InvoiceLineResponse",
 *     properties={
 *         @OA\Property(property="id", type="string", example="01JFG8Q5XKZJY4BEYQ87PC2Q1Y"),
 *         @OA\Property(property="invoice_id", type="string", example="01JFG8Q5XKZJY4BEYQ87PC2Q1Y"),
 *         @OA\Property(property="product_id", type="string", example="01JFG8Q5XKZJY4BEYQ87PC2Q1Y"),
 *         @OA\Property(property="unit_price", type="number"),
 *         @OA\Property(property="discount_percentage", type="number"),
 *         @OA\Property(property="discounted_price", type="number"),
 *         @OA\Property(property="quantity", type="integer"),
 *         @OA\Property(property="product", ref="#/components/schemas/ProductResponse")
 *     }
 * )
 */
class Invoiceline extends BaseModel
{
    use HasFactory, HasUlids;

    protected $table = 'invoice_items';
    protected $fillable = ['invoice_id', 'product_id', 'unit_price', 'quantity', 'discount_percentage', 'discounted_price'];

    protected $casts = array(
        "unit_price" => "double",
        "discount_percentage" => "double",
        "discount_price" => "double"
    );

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
