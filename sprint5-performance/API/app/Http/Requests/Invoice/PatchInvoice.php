<?php

namespace app\Http\Requests\Invoice;

use App\Http\Requests\BaseFormRequest;
use App\Rules\SubscriptSuperscriptRule;

class PatchInvoice extends BaseFormRequest
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
            'invoice_date' => 'sometimes|date_format:Y-m-d',
            'billing_street' => ['sometimes', 'string', 'max:70', new SubscriptSuperscriptRule()],
            'billing_city' => ['sometimes', 'string', 'max:40', new SubscriptSuperscriptRule()],
            'billing_state' => ['sometimes', 'string', 'max:40', new SubscriptSuperscriptRule()],
            'billing_country' => ['sometimes', 'string', 'max:40', new SubscriptSuperscriptRule()],
            'billing_postal_code' => ['sometimes', 'string', 'max:10', new SubscriptSuperscriptRule()],
            'cart_id' => 'sometimes'
        ];
    }
}
