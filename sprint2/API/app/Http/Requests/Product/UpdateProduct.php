<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseFormRequest;

class UpdateProduct extends BaseFormRequest
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
            'name' => 'string|max:120',
            'description' => 'string|max:1250',
            'price' => '',
            'category_id' => '',
            'brand_id' => ''
        ];
    }
}
