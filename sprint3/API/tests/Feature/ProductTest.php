<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tests\TestCase;

class ProductTest extends TestCase {
    use DatabaseMigrations;

    private const PRODUCTS = '/products';
    private const PRODUCTS_PREFIX = '/products/';

    public function testRetrieveProducts() {
        $this->addProduct();

        $response = $this->get(self::PRODUCTS);

        $response
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'name',
                        'description',
                        'price',
                        'name',
                    ]
                ]
            ]);
    }

    public function testRetrieveProductsByCategory() {
        $this->addProduct();

        $response = $this->get('/products?by_category=category-name');

        $response
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'name',
                        'description',
                        'price',
                        'name',
                    ]
                ]
            ]);
    }

    public function testRetrieveProductsByCategorySlug() {
        $this->addProduct();

        $response = $this->get('/products?by_category_slug=category-slug');

        $response
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'name',
                        'description',
                        'price',
                        'name',
                    ]
                ]
            ]);
    }

    public function testRetrieveProductsByBrand() {
        $this->addProduct();

        $response = $this->get('/products?by_brand=brand-name');

        $response
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'name',
                        'description',
                        'price',
                        'name',
                    ]
                ]
            ]);
    }

    public function testRetrieveRentals() {
        $this->addProduct();

        $response = $this->get('/products?by_category_slug=category-slug&is_rental=true');

        $response
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'name',
                        'description',
                        'price',
                        'name',
                    ]
                ]
            ]);
    }

    public function testRetrieveProduct() {
        $product = $this->addProduct();

        $response = $this->get(self::PRODUCTS_PREFIX . $product->id);

        $response
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                'name',
                'description',
                'price',
                'name',
            ]);
    }

    public function testAddProduct() {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $productImage = ProductImage::factory()->create();

        $payload = ['name' => 'new',
            'description' => 'some description',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'price' => 4.99,
            'is_location_offer' => false,
            'is_rental' => false,
            'product_image_id' => $productImage->id];

        $response = $this->post(self::PRODUCTS, $payload);

        $response
            ->assertStatus(ResponseAlias::HTTP_CREATED)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'price',
                'name',
            ]);
    }

    public function testAddProductRequiredFields() {
        $response = $this->post(self::PRODUCTS);

        $response
            ->assertStatus(ResponseAlias::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'name' => ['The name field is required.'],
                'price' => ['The price field is required.'],
                'category_id' => ['The category id field is required.'],
                'brand_id' => ['The brand id field is required.']
            ]);
    }

    public function testDeleteProductUnauthorized() {
        $product = $this->addProduct();

        $this->json('DELETE', self::PRODUCTS_PREFIX . $product->id)
            ->assertStatus(ResponseAlias::HTTP_NO_CONTENT);
    }

    public function testDeleteProduct() {
        $product = $this->addProduct();

        $this->delete(self::PRODUCTS_PREFIX . $product->id)
            ->assertStatus(ResponseAlias::HTTP_NO_CONTENT);
    }

    public function testDeleteNonExistingProduct() {
        $this->delete('/products/99')
            ->assertStatus(ResponseAlias::HTTP_NOT_FOUND)
            ->assertJson([
                'message' => 'Requested item not found'
            ]);
    }

    public function testUpdateProduct() {
        $product = $this->addProduct();

        $payload = ['name' => 'new name'];

        $this->put(self::PRODUCTS_PREFIX . $product->id, $payload)
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJson([
                'success' => true
            ]);
    }

    public function testRetrieveRelatedProducts() {
        $product = $this->addProduct();

        $response = $this->get(self::PRODUCTS_PREFIX . $product->id . '/related');

        $response
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                '*' => [
                    'name',
                    'description',
                    'price',
                    'name'
                ]
            ]);
    }

    public function testSearchProduct() {
        $this->addProduct();

        $response = $this->get('/products/search?q=test-product');

        $response
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'name',
                        'description',
                        'price',
                        'name'
                    ]
                ]
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function addProduct(): \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model {
        $brand = Brand::factory()->create([
            'name' => 'brand-name',
            'slug' => 'brand-slug'
        ]);
        $category = Category::factory()->create([
            'name' => 'category-name',
            'slug' => 'category-slug'
        ]);
        $productImage = ProductImage::factory()->create();

        return Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'product_image_id' => $productImage->id,
            'name' => 'test-product']);
    }

}
