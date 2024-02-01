<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mehradsadeghi\FilterQueryString\FilterQueryString;

/**
 * @OA\Schema(
 *      schema="BaseInvoiceRequest",
 *      type="object",
 *      required={"billing_address", "billing_city", "billing_state", "billing_country", "billing_postcode", "payment_method", "payment_details", "cart_id"},
 *      @OA\Property(property="billing_address", type="string"),
 *      @OA\Property(property="billing_city", type="string"),
 *      @OA\Property(property="billing_state", type="string"),
 *      @OA\Property(property="billing_country", type="string"),
 *      @OA\Property(property="billing_postcode", type="string"),
 *      @OA\Property(property="payment_method", type="string"),
 *      @OA\Property(property="cart_id", type="string"),
 *      @OA\Property(
 *          property="payment_details",
 *          type="object",
 *          oneOf={
 *              @OA\Schema(ref="#/components/schemas/BankTransferDetails"),
 *              @OA\Schema(ref="#/components/schemas/CreditCardDetails"),
 *              @OA\Schema(ref="#/components/schemas/GiftCardDetails"),
 *              @OA\Schema(ref="#/components/schemas/BuyNowPayLaterDetails"),
 *              @OA\Schema(type="object", title="CashOnDeliveryDetails")
 *          }
 *      )
 * )
 *
 * @OA\Schema(
 *      schema="BankTransferDetails",
 *      type="object",
 *      @OA\Property(property="bank_name", type="string"),
 *      @OA\Property(property="account_name", type="string"),
 *      @OA\Property(property="account_number", type="string")
 * )
 *
 * @OA\Schema(
 *      schema="CreditCardDetails",
 *      type="object",
 *      @OA\Property(property="credit_card_number", type="string"),
 *      @OA\Property(property="expiration_date", type="string"),
 *      @OA\Property(property="cvv", type="string"),
 *      @OA\Property(property="card_holder_name", type="string")
 * )
 *
 * @OA\Schema(
 *      schema="GiftCardDetails",
 *      type="object",
 *      @OA\Property(property="gift_card_number", type="string"),
 *      @OA\Property(property="validation_code", type="string")
 * )
 *
 * @OA\Schema(
 *      schema="BuyNowPayLaterDetails",
 *      type="object",
 *      @OA\Property(property="monthly_installments", type="string")
 * )
 *
 * @OA\Schema(
 *     schema="InvoiceRequest",
 *     type="object",
 *     title="InvoiceRequest",
 *     properties={
 *         @OA\Property(property="billing_address", type="string"),
 *         @OA\Property(property="billing_city", type="string"),
 *         @OA\Property(property="billing_country", type="string"),
 *         @OA\Property(property="billing_state", type="string"),
 *         @OA\Property(property="billing_postcode", type="string"),
 *         @OA\Property(property="cart_id", type="string", example="Cash on Delivery")
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="InvoiceResponse",
 *     type="object",
 *     title="InvoiceResponse",
 *     properties={
 *         @OA\Property(property="id", type="string", example=1),
 *         @OA\Property(property="invoice_date", type="string", example="2022-10-20 09:49:45"),
 *         @OA\Property(property="invoice_number", type="string", example="INV-2022000002"),
 *         @OA\Property(property="billing_address", type="string"),
 *         @OA\Property(property="billing_city", type="string"),
 *         @OA\Property(property="billing_country", type="string"),
 *         @OA\Property(property="billing_state", type="string"),
 *         @OA\Property(property="billing_postcode", type="string"),
 *         @OA\Property(property="additional_discount_percentage", type="number"),
 *         @OA\Property(property="additional_discount_amount", type="number"),
 *         @OA\Property(property="subtotal", type="number"),
 *         @OA\Property(property="total", type="number"),
 *         @OA\Property(property="status", type="string", example="COMPLETED"),
 *         @OA\Property(property="status_message", type="string", example=""),
 *         @OA\Property(property="invoice_items", type="array", @OA\Items(ref="#/components/schemas/InvoiceLineResponse"))
 *     }
 * )
 */
class Invoice extends BaseModel
{
    use FilterQueryString, HasUlids;

    protected $filters = ['in', 'sort', 'starts_with'];
    protected $table = 'invoices';
    protected $fillable = ['user_id', 'invoice_date', 'invoice_number', 'additional_discount_percentage', 'additional_discount_amount', 'billing_address', 'billing_city', 'billing_state', 'billing_country', 'billing_postcode', 'subtotal', 'total'];
    protected $hidden = ['updated_at', 'document'];

    protected $casts = array(
        "total" => "double",
        "subtotal" => "double",
        "additional_discount_amount" => "double",
        "additional_discount_percentage" => "double"
    );

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoicelines(): HasMany
    {
        return $this->hasMany(Invoiceline::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

}
