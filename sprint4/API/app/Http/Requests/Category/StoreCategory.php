<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\BaseFormRequest;

class StoreCategory extends BaseFormRequest
{

    /**
     * Get custom validation messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return ['slug.unique' => 'A category already exists with this slug.'];
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
            'name' => 'required|string|max:120',
            'slug' => 'required|unique:categories,slug|string|max:120',
            'parent_id' => 'integer|nullable'
        ];
    }
}
