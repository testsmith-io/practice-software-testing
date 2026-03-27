<?php

namespace App\Http\Requests\Contact;

use App\Http\Requests\BaseFormRequest;

class StoreContact extends BaseFormRequest
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
            'name' => 'max:120',
            'email' => 'max:120',
            'subject' => 'required|string|max:120',
            'message' => 'required|string|max:250',
        ];
    }
}
