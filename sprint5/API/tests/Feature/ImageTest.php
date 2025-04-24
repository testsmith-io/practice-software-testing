<?php

use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

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