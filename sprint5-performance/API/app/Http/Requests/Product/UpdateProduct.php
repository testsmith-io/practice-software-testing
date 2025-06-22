<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseFormRequest;
use App\Rules\SubscriptSuperscriptRule;

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
            'name' => ['string', 'max:120', new SubscriptSuperscriptRule()],
            'description' => ['string', 'max:1250', new SubscriptSuperscriptRule()],
            'price' => '',
            'category_id' => '',
            'brand_id' => '',
            'is_location_offer' => 'boolean',
            'is_rental' => 'boolean'
        ];
    }
}
