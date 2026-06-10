<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Payments;

/**
 * Expected format of a gift card, shared by the payment pre-check
 * ({@see \App\Http\Controllers\PaymentController::check}) and the authoritative
 * order validation ({@see \App\Http\Requests\Invoice\StoreInvoice}).
 *
 * A gift card number is exactly 16 alphanumeric characters; the security
 * (validation) code is exactly 4 alphanumeric characters. Anything empty,
 * malformed, or of the wrong length (e.g. random long input) is rejected so a
 * payment with an unrealistic gift card is never processed.
 */
class GiftCard
{
    public const NUMBER_REGEX = '/^[A-Za-z0-9]{16}$/';
    public const CODE_REGEX = '/^[A-Za-z0-9]{4}$/';

    /**
     * Validation rules for the gift card number.
     */
    public static function numberRules(): array
    {
        return ['required', 'string', 'regex:' . self::NUMBER_REGEX];
    }

    /**
     * Validation rules for the gift card security/validation code.
     */
    public static function codeRules(): array
    {
        return ['required', 'string', 'regex:' . self::CODE_REGEX];
    }
}
