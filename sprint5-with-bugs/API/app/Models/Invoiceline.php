<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="InvoiceLineResponse",
 *     type="object",
 *     title="InvoiceLineResponse",
 *     properties={
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="unit_price", type="number"),
 *         @OA\Property(property="quantity", type="integer"),
 *         @OA\Property(property="product", ref="#/components/schemas/ProductResponse")
 *     }
 * )
 */
class Invoiceline extends BaseModel
{
    use hasFactory;

    protected $table = 'invoice_items';
    protected $fillable = ['invoice_id', 'product_id', 'unit_price', 'quantity'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo('App\Models\Invoices');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo('App\Models\Product');
    }
}
