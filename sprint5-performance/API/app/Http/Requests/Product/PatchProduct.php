<?php

namespace app\Http\Requests\Product;

use App\Http\Requests\BaseFormRequest;
use App\Rules\SubscriptSuperscriptRule;

class PatchProduct extends BaseFormRequest
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
            'name' => ['sometimes', 'string', 'max:120', new SubscriptSuperscriptRule()],
            'description' => ['sometimes', 'string', 'max:1250', new SubscriptSuperscriptRule()],
            'price' => 'numeric|sometimes',
            'category_id' => 'sometimes',
            'brand_id' => 'sometimes',
            'is_location_offer' => 'sometimes|boolean',
            'is_rental' => 'sometimes|boolean',
            'product_image_id' => 'sometimes|string'
        ];
    }
}
