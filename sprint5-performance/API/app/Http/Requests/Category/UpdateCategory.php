<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\BaseFormRequest;
use App\Rules\SubscriptSuperscriptRule;

class UpdateCategory extends BaseFormRequest
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
            'slug' => ['alpha_dash:ascii', 'string', 'max:120', new SubscriptSuperscriptRule()],
            'parent_id' => 'string|nullable'
        ];
    }
}
