<?php

namespace App\Http\Requests;

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
            'first_name' => 'required|string|max:40',
            'last_name' => 'required|string|max:20',
            'address' => 'required|string|max:70',
            'city' => 'required|string|max:40',
            'state' => 'string|max:40',
            'country' => 'required|string|max:40',
            'postcode' => 'string|max:10',
            'phone' => 'string|max:24',
            'dob' => 'required|date|before:' . $before,
            'email' => 'required|unique:users,email|string|max:60',
            'password' => 'required|string|max:255'
        ];
    }
}
