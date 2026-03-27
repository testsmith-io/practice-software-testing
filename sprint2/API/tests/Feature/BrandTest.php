<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tests\TestCase;

class BrandTest extends TestCase {
    use DatabaseMigrations;

    public function testRetrieveBrands(): void {
        $response = $this->get('/brands');

        $response->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                '*' => [
                    'name',
                    'slug'
                ]
            ]);
    }

    public function testRetrieveBrand(): void {
        Brand::factory()->create();

        $response = $this->get('/brands/1');

        $response->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                'name',
                'slug'
            ]);
    }

    public function testAddBrand(): void {
        $payload = [
            'name' => $this->faker->name,
            'slug' => $this->faker->slug
        ];

        $response = $this->post('/brands', $payload);

        $response->assertStatus(ResponseAlias::HTTP_CREATED)
            ->assertJsonStructure([
                'id',
                'name',
                'slug'
            ]);
    }

    public function testAddBrandRequiredFields(): void {
        $response = $this->post('/brands');

        $response
            ->assertStatus(ResponseAlias::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'name' => ['The name field is required.'],
                'slug' => ['The slug field is required.']
            ]);
    }

    public function testDeleteBrand() {
        $brand = Brand::factory()->create();

        $this->json('DELETE', '/brands/' . $brand->id)
            ->assertStatus(ResponseAlias::HTTP_NO_CONTENT);
    }

    public function testDeleteNonExistingBrand() {
        $this->delete('/brands/99')
            ->assertStatus(ResponseAlias::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'id' => ['The selected id is invalid.']
            ]);
    }

    public function testDeleteBrandThatIsInUse() {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $productImage = ProductImage::factory()->create();

        Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'product_image_id' => $productImage->id]);


        $this->json('DELETE', '/brands/' . $brand->id)
            ->assertStatus(ResponseAlias::HTTP_CONFLICT);
    }

    public function testUpdateBrand() {
        $brand = Brand::factory()->create();

        $payload = ['name' => 'new name'];

        $this->put('/brands/' . $brand->id, $payload)
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJson([
                'success' => true
            ]);
    }

    public function testSearchBrand() {
        Brand::factory()->create(['name' => 'brandname']);

        $this->get('/brands/search?q=brandname')
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                '*' => [
                    'name',
                    'slug'
                ]
            ]);
    }
}
