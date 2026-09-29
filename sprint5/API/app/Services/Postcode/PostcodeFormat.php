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
    private const PATTERN_4_DIGITS = '/^\d{4}$/';
    private const PATTERN_5_DIGITS = '/^\d{5}$/';

    private const PATTERNS = [
        'AL' => self::PATTERN_4_DIGITS,
        'AT' => self::PATTERN_4_DIGITS,
        'AU' => self::PATTERN_4_DIGITS,
        'BE' => self::PATTERN_4_DIGITS,
        'BR' => '/^\d{5}-?\d{3}$/',
        'CA' => '/^[A-Za-z]\d[A-Za-z]\s?\d[A-Za-z]\d$/',
        'CH' => self::PATTERN_4_DIGITS,
        'CN' => '/^\d{6}$/',
        'CZ' => '/^\d{3}\s?\d{2}$/',
        'DE' => self::PATTERN_5_DIGITS,
        'DK' => self::PATTERN_4_DIGITS,
        'ES' => self::PATTERN_5_DIGITS,
        'FI' => self::PATTERN_5_DIGITS,
        'FR' => self::PATTERN_5_DIGITS,
        'GB' => '/^[A-Za-z]{1,2}\d[A-Za-z\d]?\s?\d[A-Za-z]{2}$/',
        'IE' => '/^[A-Za-z]\d{2}\s?[A-Za-z\d]{4}$/',
        'IT' => self::PATTERN_5_DIGITS,
        'JP' => '/^\d{3}-?\d{4}$/',
        'NL' => '/^\d{4}\s?[A-Za-z]{2}$/',
        'NO' => self::PATTERN_4_DIGITS,
        'NZ' => self::PATTERN_4_DIGITS,
        'PL' => '/^\d{2}-\d{3}$/',
        'PT' => '/^\d{4}(-\d{3})?$/',
        'RU' => '/^\d{6}$/',
        'SE' => '/^\d{3}\s?\d{2}$/',
        'TR' => self::PATTERN_5_DIGITS,
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
