<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Services\Postcode;

class PostcodeLookupResult
{
    public function __construct(
        public readonly string $street,
        public readonly string $house_number,
        public readonly string $city,
        public readonly string $state,
        public readonly string $country,
        public readonly string $postcode,
    ) {
    }

    public function toArray(): array
    {
        return [
            'street' => $this->street,
            'house_number' => $this->house_number,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postcode' => $this->postcode,
        ];
    }
}
