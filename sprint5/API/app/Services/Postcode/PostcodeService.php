<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Services\Postcode;

use Illuminate\Contracts\Config\Repository as Config;
use InvalidArgumentException;

class PostcodeService
{
    public function __construct(private readonly Config $config)
    {
    }

    public function lookup(string $country, string $postcode, ?string $houseNumber = null, ?string $overrideUrl = null): PostcodeLookupResult
    {
        return $this->driver($overrideUrl)->lookup($country, $postcode, $houseNumber);
    }

    private function driver(?string $overrideUrl): PostcodeDriver
    {
        // Runtime override (set via admin UI in local/Docker). The controller is
        // responsible for accepting the override only when APP_ENV != production.
        if ($overrideUrl !== null && $overrideUrl !== '') {
            $timeout = (int) $this->config->get('services.postcode.timeout', 5);
            return new HttpPostcodeDriver($overrideUrl, $timeout);
        }

        $driver = (string) $this->config->get('services.postcode.driver', 'faker');

        return match ($driver) {
            'faker' => new FakerPostcodeDriver(),
            'http' => $this->makeHttpDriver(),
            default => throw new InvalidArgumentException("Unknown postcode driver: {$driver}"),
        };
    }

    private function makeHttpDriver(): HttpPostcodeDriver
    {
        $url = (string) $this->config->get('services.postcode.url', '');
        if ($url === '') {
            throw new InvalidArgumentException('POSTCODE_LOOKUP_URL must be set when driver=http');
        }
        $timeout = (int) $this->config->get('services.postcode.timeout', 5);

        return new HttpPostcodeDriver($url, $timeout);
    }
}
