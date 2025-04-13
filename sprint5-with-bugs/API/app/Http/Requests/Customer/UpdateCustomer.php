<?php

namespace app\Http\Requests\Customer;

use App\Http\Requests\BaseFormRequest;

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
            'first_name' => 'required|string|max:40',
            'last_name' => 'required|string|max:20',
            'address' => 'required|string|max:70',
            'city' => 'required|string|max:40',
            'state' => 'nullable|string|max:40',
            'country' => 'required|string|max:40',
            'postcode' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:24',
            'email' => 'required|string|max:60',
            'role' => 'in:user,admin'
        ];
    }
}
