<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenApi\Analysers\AttributeAnnotationFactory;
use OpenApi\Analysers\DocBlockAnnotationFactory;
use OpenApi\Analysers\ReflectionAnalyser;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // l5-swagger 11 defaults to an attributes-only analyser, but the API
        // is documented with @OA docblock annotations. The analyser must be
        // set here rather than in config/l5-swagger.php because the deploy
        // runs `config:cache`, which cannot serialize objects.
        config(['l5-swagger.defaults.scanOptions.analyser' => new ReflectionAnalyser([
            new DocBlockAnnotationFactory(),
            new AttributeAnnotationFactory(),
        ])]);
    }
}
