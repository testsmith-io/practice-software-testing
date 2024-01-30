<?php

namespace App\Http\Requests\Brand;

use App\Http\Requests\BaseFormRequest;
use App\Rules\SubscriptSuperscriptRule;

class StoreBrand extends BaseFormRequest
{

    /**
     * Get custom validation messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return ['slug.unique' => 'A brand already exists with this slug.'];
    }

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
            'name' => ['required', 'string', 'max:120', new SubscriptSuperscriptRule()],
            'slug' => ['required', 'unique:brands,slug', 'string', 'max:120', new SubscriptSuperscriptRule()]
        ];
    }
}
