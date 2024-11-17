<?php

namespace app\Http\Requests\Category;

use App\Http\Requests\BaseFormRequest;
use App\Rules\SubscriptSuperscriptRule;

class PatchCategory extends BaseFormRequest
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
            'slug' => ['sometimes', 'alpha_dash:ascii', 'required', 'unique:categories,slug', 'string', 'max:120', new SubscriptSuperscriptRule()],
            'parent_id' => 'string|nullable'
        ];
    }
}
