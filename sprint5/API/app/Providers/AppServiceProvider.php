<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Providers;

use App\Swagger\DocBlockGeneratorFactory;
use Illuminate\Support\ServiceProvider;
use L5Swagger\GeneratorFactory;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Swap in a factory that enables docblock @OA annotations; see
        // DocBlockGeneratorFactory for why this is not done in config.
        $this->app->bind(GeneratorFactory::class, DocBlockGeneratorFactory::class);
    }
}
