<?php

namespace App\Http\Requests\Invoice;

use App\Http\Requests\BaseFormRequest;
use App\Rules\SubscriptSuperscriptRule;
use Illuminate\Validation\Rule;

class StoreInvoice extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'payment_method' => ['required', Rule::in(['bank-transfer', 'cash-on-delivery', 'credit-card', 'buy-now-pay-later', 'gift-card'])],
            "payment_details" => ['present'],
            'invoice_date' => 'date_format:Y-m-d',
            'billing_street' => ['required', 'string', 'max:70', new SubscriptSuperscriptRule()],
            'billing_city' => ['required', 'string', 'max:40', new SubscriptSuperscriptRule()],
            'billing_state' => ['string', 'max:40', new SubscriptSuperscriptRule()],
            'billing_country' => ['required', 'string', 'max:40', new SubscriptSuperscriptRule()],
            'billing_postal_code' => ['string', 'max:10', new SubscriptSuperscriptRule()],
            'cart_id' => 'required'
        ];
    }
}
