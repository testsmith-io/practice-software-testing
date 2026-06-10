<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Http\Requests\Invoice;

use App\Http\Requests\BaseFormRequest;
use App\Payments\GiftCard;
use App\Rules\AddressMatchesCountry;
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
            'billing_country' => ['required', 'string', 'max:40', new SubscriptSuperscriptRule(), new AddressMatchesCountry()],
            'billing_postal_code' => ['string', 'max:10', new SubscriptSuperscriptRule()],
            'cart_id' => 'required'
        ];
    }

    /**
     * When paying by gift card, the card number and security code must match
     * the expected format before the order (and therefore the payment) is
     * processed.
     */
    public function withValidator($validator): void
    {
        $isGiftCard = fn ($input) => $input->payment_method === 'gift-card';

        $validator->sometimes('payment_details.gift_card_number', GiftCard::numberRules(), $isGiftCard);
        $validator->sometimes('payment_details.validation_code', GiftCard::codeRules(), $isGiftCard);
    }
}
