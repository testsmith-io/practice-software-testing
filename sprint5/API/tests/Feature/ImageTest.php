<?php

use App\Models\ProductImage;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

uses(DatabaseMigrations::class);

//covers(ImageController::class);

test('retrieve images', function () {
    ProductImage::factory()->create();

    $response = $this->getJson('/images');

    $response
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            '*' => [
                'id',
                'by_name',
                'by_url',
                'source_name',
                'source_url',
                'file_name',
                'title'
            ]
        ]);
});
