<?php

namespace App\Http\Requests\Brand;

use App\Http\Requests\BaseFormRequest;
use App\Rules\SubscriptSuperscriptRule;

class UpdateBrand extends BaseFormRequest
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
            'slug' => ['string', 'max:120', new SubscriptSuperscriptRule()]
        ];
    }
}
