<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

uses(DatabaseMigrations::class);

//covers(BrandController::class);

test('retrieve brands', function () {
    $response = $this->getJson('/brands');

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            '*' => [
                'name',
                'slug'
            ]
        ]);
});

test('retrieve brand', function () {
    $brand = Brand::factory()->create();

    $response = $this->getJson("/brands/{$brand->id}");

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            'name',
            'slug'
        ]);
});

test('add brand', function () {
    $payload = [
        'name' => $this->faker->name,
        'slug' => $this->faker->slug
    ];

    $response = $this->postJson('/brands', $payload);

    $response->assertStatus(ResponseAlias::HTTP_CREATED)
        ->assertJsonStructure([
            'id',
            'name',
            'slug'
        ]);
});

test('add brand required fields', function () {
    $response = $this->postJson('/brands');

    $response
        ->assertStatus(ResponseAlias::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'name' => ['The name field is required.'],
            'slug' => ['The slug field is required.']
        ]);
});

test('delete brand unauthorized', function () {
    $brand = Brand::factory()->create();

    $this->json('DELETE', "/brands/{$brand->id}")
        ->assertStatus(ResponseAlias::HTTP_UNAUTHORIZED);
});

test('delete brand', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $brand = Brand::factory()->create();

    $this->json('DELETE', "/brands/{$brand->id}", [], $this->headers($admin))
        ->assertStatus(ResponseAlias::HTTP_NO_CONTENT);

    $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
});

test('delete non existing brand', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->deleteJson('/brands/99', [], $this->headers($admin))
        ->assertStatus(ResponseAlias::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'id' => ['The selected id is invalid.']
        ]);
});

test('delete brand that is in use', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $brand = Brand::factory()->create();
    $category = Category::factory()->create();
    $productImage = ProductImage::factory()->create();

    Product::factory()->create([
        'brand_id' => $brand->id,
        'category_id' => $category->id,
        'product_image_id' => $productImage->id]);

    $this->json('DELETE', "/brands/{$brand->id}", [], $this->headers($admin))
        ->assertStatus(ResponseAlias::HTTP_CONFLICT);
});

test('update brand', function () {
    $brand = Brand::factory()->create();

    $payload = ['name' => 'new name'];

    $this->putJson("/brands/{$brand->id}", $payload)
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertExactJson([
            'success' => true,
        ]);
});

test('partial update brand', function () {
    $brand = Brand::factory()->create();

    $payload = ['name' => 'new name'];

    $this->patchJson("/brands/{$brand->id}", $payload)
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertExactJson([
            'success' => true,
        ]);

    $this->assertDatabaseHas('brands', [
        'id' => $brand->id,
        'name' => 'new name'
    ]);
});

test('search brand', function () {
    Brand::factory()->create(['name' => 'brandname']);

    $this->getJson('/brands/search?q=brandname')
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            '*' => [
                'name',
                'slug'
            ]
        ]);
});

test('deleting a brand used by products returns 409 with appropriate message', function () {
    $brand = Brand::factory()->create();
    $admin = User::factory()->create(['role' => 'admin']);
    Product::factory()->create(['brand_id' => $brand->id]);

    $response = $this->deleteJson("/brands/{$brand->id}", [], $this->headers($admin));

    $response->assertStatus(ResponseAlias::HTTP_CONFLICT);
    $response->assertJson([
        'success' => false,
        'message' => 'Seems like this brand is used elsewhere.',
    ]);
});
