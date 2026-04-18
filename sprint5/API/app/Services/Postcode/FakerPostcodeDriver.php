<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Services\Postcode;

use Faker\Factory;

class FakerPostcodeDriver implements PostcodeDriver
{
    public function lookup(string $country, string $postcode, ?string $houseNumber): PostcodeLookupResult
    {
        // Deterministic output: same inputs always yield the same address,
        // so demos, screenshots, and tests stay stable.
        $seed = crc32(strtolower("{$country}|{$postcode}|{$houseNumber}"));
        $faker = Factory::create();
        $faker->seed($seed);

        $house = $houseNumber !== null && $houseNumber !== ''
            ? $houseNumber
            : (string) $faker->numberBetween(1, 250);

        return new PostcodeLookupResult(
            street: $faker->streetName() . ' ' . $house,
            city: $faker->city(),
            state: $faker->state(),
            country: $country,
            postcode: strtoupper($postcode),
        );
    }
}
