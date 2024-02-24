<?php

namespace App\Services;

use Haruncpi\LaravelIdGenerator\IdGenerator;

class InvoiceNumberGenerator
{
    public function generate(array $config)
    {
        return IdGenerator::generate($config);
    }
}
