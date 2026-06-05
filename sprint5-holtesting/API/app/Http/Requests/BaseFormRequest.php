<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.


namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseFormRequest extends FormRequest
{

    protected function failedValidation(Validator $validator)
    {
        // If every failing rule is a uniqueness check, this is a state conflict
        // (the resource already exists), not a malformed request — return 409.
        // Mixed shape+unique failures take the 422 path so shape errors aren't
        // hidden behind a conflict status.
        $status = $this->isPureUniquenessFailure($validator) ? 409 : 422;
        throw new HttpResponseException(response()->json($validator->errors(), $status));
    }

    private function isPureUniquenessFailure(Validator $validator): bool
    {
        $failed = $validator->failed();
        if (empty($failed)) {
            return false;
        }
        foreach ($failed as $rules) {
            foreach ($rules as $ruleName => $_parameters) {
                if ($ruleName !== 'Unique') {
                    return false;
                }
            }
        }
        return true;
    }
}
