<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseFormRequest;
use App\Rules\SubscriptSuperscriptRule;

class StoreProduct extends BaseFormRequest
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
            'name' => ['required', 'string', 'max:120', new SubscriptSuperscriptRule()],
            'description' => ['string', 'max:1250', new SubscriptSuperscriptRule()],
            'price' => 'numeric|required',
            'category_id' => 'required',
            'brand_id' => 'required',
            'is_location_offer' => 'required|boolean',
            'is_rental' => 'required|boolean',
            'product_image_id' => 'required|string'
        ];
    }
}
