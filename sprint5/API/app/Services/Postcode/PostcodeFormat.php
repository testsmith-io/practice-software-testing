<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Services\Postcode;

/**
 * Lightweight, country-aware postcode format check. Faker cannot tie a postcode
 * to a specific city, but it can keep the whole address internally consistent:
 * the postcode the customer types must at least have the right shape for the
 * selected country (so a Dutch "1011AB" under Austria is rejected).
 *
 * Coverage mirrors the countries the lookup actually supports
 * ({@see FakerPostcodeDriver::COUNTRY_TO_LOCALE}). For any country not listed
 * here the format is left unconstrained, so exotic countries are never
 * spuriously rejected.
 */
class PostcodeFormat
{
    private const PATTERNS = [
        'AL' => '/^\d{4}$/',
        'AT' => '/^\d{4}$/',
        'AU' => '/^\d{4}$/',
        'BE' => '/^\d{4}$/',
        'BR' => '/^\d{5}-?\d{3}$/',
        'CA' => '/^[A-Za-z]\d[A-Za-z]\s?\d[A-Za-z]\d$/',
        'CH' => '/^\d{4}$/',
        'CN' => '/^\d{6}$/',
        'CZ' => '/^\d{3}\s?\d{2}$/',
        'DE' => '/^\d{5}$/',
        'DK' => '/^\d{4}$/',
        'ES' => '/^\d{5}$/',
        'FI' => '/^\d{5}$/',
        'FR' => '/^\d{5}$/',
        'GB' => '/^[A-Za-z]{1,2}\d[A-Za-z\d]?\s?\d[A-Za-z]{2}$/',
        'IE' => '/^[A-Za-z]\d{2}\s?[A-Za-z\d]{4}$/',
        'IT' => '/^\d{5}$/',
        'JP' => '/^\d{3}-?\d{4}$/',
        'NL' => '/^\d{4}\s?[A-Za-z]{2}$/',
        'NO' => '/^\d{4}$/',
        'NZ' => '/^\d{4}$/',
        'PL' => '/^\d{2}-\d{3}$/',
        'PT' => '/^\d{4}(-\d{3})?$/',
        'RU' => '/^\d{6}$/',
        'SE' => '/^\d{3}\s?\d{2}$/',
        'TR' => '/^\d{5}$/',
        'US' => '/^\d{5}(-\d{4})?$/',
    ];

    /**
     * Whether a format is known for this country (otherwise it is unconstrained).
     */
    public static function isKnown(string $country): bool
    {
        return isset(self::PATTERNS[strtoupper(trim($country))]);
    }

    /**
     * True when the postcode has a valid shape for the country, or when the
     * country has no known format (in which case anything is accepted).
     */
    public static function matches(string $country, string $postcode): bool
    {
        $pattern = self::PATTERNS[strtoupper(trim($country))] ?? null;

        if ($pattern === null) {
            return true;
        }

        return (bool) preg_match($pattern, trim($postcode));
    }
}
