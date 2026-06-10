<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Services\Postcode;

use Faker\Factory;

class FakerPostcodeDriver implements PostcodeDriver
{
    /**
     * Map ISO-3166 alpha-2 country codes to a Faker locale.
     * Unknown codes fall back to en_US so results still look plausible.
     */
    private const COUNTRY_TO_LOCALE = [
        'NL' => 'nl_NL',
        'BE' => 'nl_BE',
        'DE' => 'de_DE',
        'AT' => 'de_AT',
        'CH' => 'de_CH',
        'FR' => 'fr_FR',
        'ES' => 'es_ES',
        'IT' => 'it_IT',
        'PT' => 'pt_PT',
        'BR' => 'pt_BR',
        'GB' => 'en_GB',
        'IE' => 'en_IE',
        'US' => 'en_US',
        'CA' => 'en_CA',
        'AU' => 'en_AU',
        'NZ' => 'en_NZ',
        'SE' => 'sv_SE',
        'NO' => 'nb_NO',
        'DK' => 'da_DK',
        'FI' => 'fi_FI',
        'PL' => 'pl_PL',
        'CZ' => 'cs_CZ',
        'RU' => 'ru_RU',
        'JP' => 'ja_JP',
        'CN' => 'zh_CN',
        'TR' => 'tr_TR',
    ];

    public function lookup(string $country, string $postcode, ?string $houseNumber): PostcodeLookupResult
    {
        $locale = self::COUNTRY_TO_LOCALE[strtoupper($country)] ?? 'en_US';

        // The locality (street/city/state) is a property of the country +
        // postcode only — the house number must NOT change which city you live
        // in. Keeping it out of the seed means a server-side re-lookup (which
        // only has the country + postcode) reproduces the exact same locality
        // the customer saw at checkout, which is what the address/country
        // consistency check relies on. Output stays deterministic so demos,
        // screenshots, and tests remain stable.
        $localitySeed = crc32(strtolower("{$country}|{$postcode}"));
        $faker = Factory::create($locale);
        $faker->seed($localitySeed);

        $street = $faker->streetName();
        $city = $faker->city();
        $state = $this->state($faker);

        $house = $houseNumber !== null && $houseNumber !== ''
            ? $houseNumber
            : (string) $faker->numberBetween(1, 250);

        return new PostcodeLookupResult(
            street: $street,
            house_number: $house,
            city: $city,
            state: $state,
            country: strtoupper($country),
            postcode: strtoupper($postcode),
        );
    }

    /**
     * Not every Faker locale ships a state/region list — for those, state()
     * throws. Treat a missing region as simply "no state" instead of letting
     * the whole lookup fail.
     */
    private function state(\Faker\Generator $faker): string
    {
        try {
            return (string) $faker->state();
        } catch (\InvalidArgumentException $e) {
            return '';
        }
    }
}
