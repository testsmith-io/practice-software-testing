<?php

namespace app\Http\Requests\Customer;

use App\Http\Requests\BaseFormRequest;
use App\Rules\SubscriptSuperscriptRule;
use Carbon\Carbon;
use Illuminate\Validation\Rules\Password;

class StoreCustomer extends BaseFormRequest
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
            'dob.after' => 'Customer must be younger than 75 years old.',
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
        $after = $dt->subYears(75)->format('Y-m-d');

        return [
            'first_name' => ['required', 'string', 'max:40', new SubscriptSuperscriptRule()],
            'last_name' => ['required', 'string', 'max:20', new SubscriptSuperscriptRule()],
            'address' => ['array'],
            'address.street' => ['string', 'max:70', new SubscriptSuperscriptRule()],
            'address.city' => ['string', 'max:40', new SubscriptSuperscriptRule()],
            'address.state' => ['string', 'max:40', new SubscriptSuperscriptRule()],
            'address.country' => ['string', 'max:40', new SubscriptSuperscriptRule()],
            'address.postal_code' => ['string', 'max:10', new SubscriptSuperscriptRule()],
            'phone' => ['string', 'max:24', new SubscriptSuperscriptRule()],
            'dob' => ['date', 'date_format:Y-m-d', "before:{$before}", "after:{$after}"],
            'email' => ['required', 'unique:users,email', 'string', 'max:256', new SubscriptSuperscriptRule()],
            'password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised(), new SubscriptSuperscriptRule()]
        ];
    }
}
