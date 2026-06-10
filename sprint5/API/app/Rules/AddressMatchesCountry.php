<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Rules;

use App\Services\Postcode\PostcodeFormat;
use App\Services\Postcode\PostcodeService;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use RuntimeException;

/**
 * Cross-field check that the submitted city/state actually belong to the
 * selected country. The postcode lookup is the source of truth: for a given
 * country + postcode it returns the expected locality, so an address that was
 * tampered with after the auto-fill (e.g. an Austrian city while Albania is
 * selected) no longer matches and is rejected.
 *
 * Attach this to the country attribute; it reads the sibling postcode/city/state
 * fields from the request via {@see DataAwareRule}.
 */
class AddressMatchesCountry implements ValidationRule, DataAwareRule
{
    private array $data = [];

    public function __construct(
        private readonly string $postalCodeField = 'billing_postal_code',
        private readonly string $cityField = 'billing_city',
        private readonly string $stateField = 'billing_state',
    ) {
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $country = is_string($value) ? trim($value) : '';
        $postcode = trim((string) ($this->data[$this->postalCodeField] ?? ''));
        $city = trim((string) ($this->data[$this->cityField] ?? ''));
        $state = trim((string) ($this->data[$this->stateField] ?? ''));

        // Without a country + postcode there is nothing to validate against.
        if ($country === '' || $postcode === '') {
            return;
        }

        // The postcode must at least have the right shape for the country, so
        // a foreign-format code (e.g. a Dutch "1011AB" while Austria is
        // selected) is rejected.
        if (!PostcodeFormat::matches($country, $postcode)) {
            $fail("The {$attribute} does not match the entered address. The postal code format is not valid for the selected country.");

            return;
        }

        try {
            // City/state are a property of country + postcode only, so the
            // house number is intentionally omitted here (and ignored by the
            // driver for locality purposes).
            $expected = app(PostcodeService::class)->lookup($country, $postcode);
        } catch (RuntimeException $e) {
            // Lookup backend unavailable (e.g. the http driver is down). Fail
            // open so a transient outage never blocks a checkout.
            return;
        }

        if ($city !== '' && $expected->city !== '' && !$this->matches($city, $expected->city)) {
            $fail("The {$attribute} does not match the entered address. The city does not belong to the selected country.");

            return;
        }

        if ($state !== '' && $expected->state !== '' && !$this->matches($state, $expected->state)) {
            $fail("The {$attribute} does not match the entered address. The state does not belong to the selected country.");
        }
    }

    private function matches(string $submitted, string $expected): bool
    {
        return $this->normalize($submitted) === $this->normalize($expected);
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim($value));
    }
}