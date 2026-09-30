<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Swagger;

use L5Swagger\Generator;
use L5Swagger\GeneratorFactory;
use OpenApi\Analysers\AttributeAnnotationFactory;
use OpenApi\Analysers\DocBlockAnnotationFactory;
use OpenApi\Analysers\ReflectionAnalyser;

/**
 * l5-swagger 11 defaults to an attributes-only analyser, but this API is
 * documented with @OA docblock annotations. The analyser is injected here,
 * at generation time, because it is an object: placing it in the l5-swagger
 * config would break `php artisan config:cache`, which cannot serialize
 * objects.
 */
class DocBlockGeneratorFactory extends GeneratorFactory
{
    public function make(string $documentation): Generator
    {
        config(["l5-swagger.documentations.{$documentation}.scanOptions.analyser" => new ReflectionAnalyser([
            new DocBlockAnnotationFactory(),
            new AttributeAnnotationFactory(),
        ])]);

        return parent::make($documentation);
    }
}
