<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Http\Requests\Brand;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

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
            'name' => 'required|string|max:120',
            'slug' => ['required', 'string', 'max:120', Rule::unique('brands', 'slug')->ignore($this->route('id'))],
        ];
    }
}
