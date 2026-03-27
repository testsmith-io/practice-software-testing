<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Services;

use Haruncpi\LaravelIdGenerator\IdGenerator;

class InvoiceNumberGenerator
{
    public function generate(array $config)
    {
        return IdGenerator::generate($config);
    }
}
