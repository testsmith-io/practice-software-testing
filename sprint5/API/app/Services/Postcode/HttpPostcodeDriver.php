<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Services\Postcode;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class HttpPostcodeDriver implements PostcodeDriver
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly int $timeout,
    ) {
    }

    public function lookup(string $country, string $postcode, ?string $houseNumber): PostcodeLookupResult
    {
        $response = Http::timeout($this->timeout)
            ->acceptJson()
            ->get(rtrim($this->baseUrl, '/') . '/lookup', array_filter([
                'country' => $country,
                'postcode' => $postcode,
                'house_number' => $houseNumber,
            ], fn ($v) => $v !== null && $v !== ''));

        if ($response->failed()) {
            Log::warning('Postcode lookup failed', [
                'status' => $response->status(),
                'country' => $country,
                'postcode' => $postcode,
            ]);
            throw new RuntimeException('Postcode lookup failed with status ' . $response->status());
        }

        $data = $response->json();

        return new PostcodeLookupResult(
            street: (string) ($data['street'] ?? ''),
            house_number: (string) ($data['house_number'] ?? $houseNumber ?? ''),
            city: (string) ($data['city'] ?? ''),
            state: (string) ($data['state'] ?? ''),
            country: (string) ($data['country'] ?? $country),
            postcode: (string) ($data['postcode'] ?? $postcode),
        );
    }
}
