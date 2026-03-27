<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SubscriptSuperscriptRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     */
    public function validate(string $attribute, $value, Closure $fail): void
    {
        // Regular expression to match subscript/superscript characters
        $pattern = '/[\x{2070}-\x{209F}\x{2080}-\x{209F}]/u';

        if (preg_match($pattern, $value)) {
            $fail("The {$attribute} must not contain subscript/superscript characters.");
        }
    }
}
