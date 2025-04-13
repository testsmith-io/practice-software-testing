<?php

namespace app\Http\Requests\Customer;

use App\Http\Requests\BaseFormRequest;
use Carbon\Carbon;

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
            'email.unique' => 'User already registered - Your password hint is: Name of your cat!'];
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
        $after = $dt->subYears(76)->format('Y-m-d');

        return [
            'first_name' => 'required|regex:/^[a-zA-Z]+$/|max:40',
            'last_name' => 'required|regex:/^[a-zA-Z]+$/|max:20',
            'address' => 'required|string|max:70',
            'city' => 'required|string|max:40',
            'state' => 'string|max:40',
            'country' => 'required|string|max:40',
            'postcode' => 'string|max:10',
            'phone' => 'string|max:24',
            'dob' => ['date', 'date_format:Y-m-d', "before:{$before}", "after:{$after}"],
            'email' => 'required|unique:users,email|string|max:60',
            'password' => 'required|string|min:9|max:40'
        ];
    }
}
