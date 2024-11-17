<?php

namespace app\Http\Requests\Brand;

use App\Http\Requests\BaseFormRequest;
use App\Rules\SubscriptSuperscriptRule;

class PatchBrand extends BaseFormRequest
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
            'name' => ['sometimes', 'string', 'max:120', new SubscriptSuperscriptRule()],
            'slug' => ['sometimes', 'alpha_dash:ascii','unique:brands,slug', 'string', 'max:120', new SubscriptSuperscriptRule()]
        ];
    }
}
