<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

uses(DatabaseMigrations::class);

//covers(ProductController::class);

test('retrieve products', function () {
    $product = addProduct();

    $response = $this->getJson('/products');

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
});

test('retrieve products by category', function () {
    addProduct();

    $response = $this->getJson('/products?by_category=category-name');

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
});

test('retrieve products by category slug', function () {
    addProduct();

    $response = $this->getJson('/products?by_category_slug=category-slug');

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
});

test('retrieve products by brand', function () {
    addProduct();

    $response = $this->getJson('/products?by_brand=brand-name');

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
});

test('retrieve products by query', function () {
    addProduct();

    $response = $this->getJson('/products?q=test-product');

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
});

test('retrieve rentals', function () {
    addProduct();

    $response = $this->getJson('/products?by_category_slug=category-slug&is_rental=true');

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
});

test('retrieve product', function () {
    $product = addProduct();

    $response = $this->getJson("/products/{$product->id}");

    $response
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            'name',
            'description',
            'price',
            'name',
        ]);
});

test('add product', function () {
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

    $response = $this->postJson('/products', $payload);

    //        $response->dump();
    $response
        ->assertStatus(ResponseAlias::HTTP_CREATED)
        ->assertJsonStructure([
            'id',
            'name',
            'description',
            'price',
            'name',
        ]);
});

test('add product required fields', function () {
    $response = $this->postJson('/products');

    $response
        ->assertStatus(ResponseAlias::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'name' => ['The name field is required.'],
            'price' => ['The price field is required.'],
            'category_id' => ['The category id field is required.'],
            'brand_id' => ['The brand id field is required.']
        ]);
});

test('delete product unauthorized', function () {
    $product = addProduct();

    $this->json('DELETE', "/products/{$product->id}")
        ->assertStatus(ResponseAlias::HTTP_UNAUTHORIZED);
});

test('delete product', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $product = addProduct();

    $this->deleteJson("/products/{$product->id}", [], $this->headers($admin))
        ->assertStatus(ResponseAlias::HTTP_NO_CONTENT);

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

test('delete non existing product', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->deleteJson('/products/99', [], $this->headers($admin))
        ->assertStatus(ResponseAlias::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'id' => ['The selected id is invalid.']
        ]);
});

test('delete product in use', function () {
    $invoice = Invoice::factory()->create([
        'total' => 150.00,
        'billing_country' => 'The Netherlands'
    ]);

    $admin = User::factory()->create(['role' => 'admin']);

    $this->deleteJson("/products/{$invoice->invoicelines[0]['product_id']}", [], $this->headers($admin))
        ->assertStatus(ResponseAlias::HTTP_CONFLICT);
});

test('update product', function () {
    $product = addProduct();

    $payload = ['name' => 'new name'];

    $this->putJson("/products/{$product->id}", $payload)
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertExactJson([
            'success' => true
        ]);
});

test('partial update product', function () {
    $product = Product::factory()->create();

    $payload = [
        'name' => 'updated product',
        'price' => 99.99
    ];

    $this->patchJson("/products/{$product->id}", $payload)
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertExactJson([
            'success' => true,
        ]);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'updated product',
        'price' => 99.99
    ]);
});

test('retrieve related products', function () {
    $product = addProduct();

    $response = $this->getJson("/products/{$product->id}/related");

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
});

test('search product', function () {
    addProduct();

    $response = $this->getJson('/products/search?q=test-product');

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
});

/**
 * @return Collection|Model
 */
function addProduct(): Collection|Model
{
    $brand = Brand::factory()->create([
        'name' => 'brand-name',
        'slug' => 'brand-slug'
    ]);
    $category = Category::factory()->create([
        'name' => 'category-name',
        'slug' => 'category-slug'
    ]);
    $productImage = ProductImage::factory()->create();

    $product = Product::factory()->create([
        'brand_id' => $brand->id,
        'category_id' => $category->id,
        'product_image_id' => $productImage->id,
        'name' => 'test-product']);
    return $product;
}
