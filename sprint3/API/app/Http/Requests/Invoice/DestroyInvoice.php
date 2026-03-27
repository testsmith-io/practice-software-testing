<?php

namespace App\Http\Requests\Invoice;

use App\Http\Requests\BaseFormRequest;

class DestroyInvoice extends BaseFormRequest
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
            'id' => 'required|exists:invoice,id',
        ];
    }

    /**
     * Use route parameters for validation
     * @return array
     */
    public function validationData(): array
    {
        return app('request')->route()->parameters;
    }
}
