<?php

namespace App\Http\Requests\Contact;

use App\Http\Requests\BaseFormRequest;
use App\Rules\SubscriptSuperscriptRule;


class StoreContactReply extends BaseFormRequest
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
            'message' => ['string', 'max:250', new SubscriptSuperscriptRule()]
        ];
    }
}
