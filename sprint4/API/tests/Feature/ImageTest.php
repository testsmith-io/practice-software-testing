<?php

namespace tests\Feature;

use App\Models\ProductImage;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tests\TestCase;

class ImageTest extends TestCase
{
    use DatabaseMigrations;

    public function testRetrieveImages()
    {
        ProductImage::factory()->create();

        $response = $this->get('/images');

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
    }

}
