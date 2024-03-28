<?php

namespace app\Http\Requests\Customer;

use App\Http\Requests\BaseFormRequest;
use App\Rules\SubscriptSuperscriptRule;
use Carbon\Carbon;

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
     * Get custom validation messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return ['dob.before' => 'Customer must be 18 years old.',
            'email.unique' => 'A customer with this email address already exists.'];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $dt = new Carbon();
        $before = $dt->subYears(18)->format('Y-m-d');

        return [
            'first_name' => ['required', 'string', 'max:40', new SubscriptSuperscriptRule()],
            'last_name' => ['required', 'string', 'max:20', new SubscriptSuperscriptRule()],
            'address' => ['required', 'string', 'max:70', new SubscriptSuperscriptRule()],
            'city' => ['required', 'string', 'max:40', new SubscriptSuperscriptRule()],
            'state' => ['nullable', 'string', 'max:40', new SubscriptSuperscriptRule()],
            'country' => ['required', 'string', 'max:40', new SubscriptSuperscriptRule()],
            'postcode' => ['nullable', 'string', 'max:10', new SubscriptSuperscriptRule()],
            'phone' => ['nullable', 'string', 'max:24', new SubscriptSuperscriptRule()],
            'dob' => ['date', 'date_format:Y-m-d',"before:{$before}"],
            'email' => ['required', 'string', 'max:256', new SubscriptSuperscriptRule()]
        ];
    }
}
