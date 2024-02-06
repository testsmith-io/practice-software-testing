<?php

namespace App\Http\Requests;

use App\Rules\SubscriptSuperscriptRule;

class UpdateCustomer extends BaseFormRequest
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
            'first_name' => ['required', 'string', 'max:40', new SubscriptSuperscriptRule()],
            'last_name' => ['required', 'string', 'max:20', new SubscriptSuperscriptRule()],
            'address' => ['required', 'string', 'max:70', new SubscriptSuperscriptRule()],
            'city' => ['required', 'string', 'max:40', new SubscriptSuperscriptRule()],
            'state' => ['nullable', 'string', 'max:40', new SubscriptSuperscriptRule()],
            'country' => ['required', 'string', 'max:40', new SubscriptSuperscriptRule()],
            'postcode' => ['nullable', 'string', 'max:10', new SubscriptSuperscriptRule()],
            'phone' => ['nullable', 'string', 'max:24', new SubscriptSuperscriptRule()],
            'email' => ['required', 'string', 'max:256', new SubscriptSuperscriptRule()]
        ];
    }
}
