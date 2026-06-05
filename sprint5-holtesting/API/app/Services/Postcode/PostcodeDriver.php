<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Services\Postcode;

interface PostcodeDriver
{
    public function lookup(string $country, string $postcode, ?string $houseNumber): PostcodeLookupResult;
}
