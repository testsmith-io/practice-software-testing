<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

uses(DatabaseMigrations::class);

//covers(CategoryController::class);

test('retrieve categories', function () {
    Category::factory()->create();

    $response = $this->getJson('/categories');

    $response
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            '*' => [
                'name',
                'slug'
            ]
        ]);
});

test('retrieve tree of categories', function () {
    Category::factory()->create();

    $response = $this->getJson('/categories/tree');

    $response
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            '*' => [
                'name',
                'slug'
            ]
        ]);
});

test('retrieve tree of categories by slug', function () {
    Category::factory()->create([
        'slug' => 'test'
    ]);

    $response = $this->getJson('/categories/tree?by_category_slug=test');

    $response
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            '*' => [
                'name',
                'slug'
            ]
        ]);
});

test('retrieve category', function () {
    $category = Category::factory()->create();

    $response = $this->getJson("/categories/tree/{$category->id}");

    $response
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            'name',
            'slug'
        ]);
});

test('add category', function () {
    $payload = ['name' => 'new',
        'slug' => 'some-description'];

    $response = $this->postJson('/categories', $payload);

    $response
        ->assertStatus(ResponseAlias::HTTP_CREATED)
        ->assertJsonStructure([
            'id',
            'name',
            'slug'
        ]);
});

test('add category required fields', function () {
    $response = $this->postJson('/categories');

    $response
        ->assertStatus(ResponseAlias::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'name' => ['The name field is required.'],
            'slug' => ['The slug field is required.']
        ]);
});

test('delete category unauthorized', function () {
    $brand = Category::factory()->create();

    $this->json('DELETE', "/categories/{$brand->id}")
        ->assertStatus(ResponseAlias::HTTP_UNAUTHORIZED);
});

test('delete category', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $category = Category::factory()->create();

    $this->deleteJson("/categories/{$category->id}", [], $this->headers($admin))
        ->assertStatus(ResponseAlias::HTTP_NO_CONTENT);
    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('delete non existing category', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->deleteJson('/categories/99', [], $this->headers($admin))
        ->assertStatus(ResponseAlias::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'id' => ['The selected id is invalid.']
        ]);
});

test('delete category that is in use', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $brand = Brand::factory()->create();
    $category = Category::factory()->create();
    $productImage = ProductImage::factory()->create();

    Product::factory()->create([
        'brand_id' => $brand->id,
        'category_id' => $category->id,
        'product_image_id' => $productImage->id]);

    $this->json('DELETE', "/categories/{$category->id}", [], $this->headers($admin))
        ->assertStatus(ResponseAlias::HTTP_CONFLICT);
});

test('update category', function () {
    $category = Category::factory()->create();

    $payload = ['name' => 'new name'];

    $response = $this->putJson("/categories/{$category->id}", $payload);

    $response
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertExactJson([
            'success' => true
        ])
        ->assertJsonStructure(['success']);
});

test('partial update category', function () {
    $category = Category::factory()->create();

    $payload = ['name' => 'updated category'];

    $this->patchJson("/categories/{$category->id}", $payload)
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertExactJson([
            'success' => true,
        ]);

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'updated category'
    ]);
});

test('search category', function () {
    Category::factory()->create(['name' => 'categoryname']);

    $this->getJson('/categories/search?q=categoryname')
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            '*' => [
                'name',
                'slug'
            ]
        ]);
});

test('deleting a category in use returns 409 conflict with message', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::factory()->create();
    Product::factory()->create(['category_id' => $category->id]);

    $response = $this->deleteJson("/categories/{$category->id}", [], $this->headers($admin));

    $response
        ->assertStatus(409)
        ->assertJson([
            'success' => false,
            'message' => 'Seems like this category is used elsewhere.',
        ])
        ->assertJsonStructure(['success', 'message']);
});

test('patch category returns success false if update fails', function () {
    $category = Category::factory()->create();
    $admin = User::factory()->create(['role' => 'admin']);
    Product::factory()->create(['category_id' => $category->id]);

    $response = $this->deleteJson("/categories/{$category->id}", [], $this->headers($admin));

    $response->assertStatus(ResponseAlias::HTTP_CONFLICT);
    $response->assertJson([
        'success' => false,
        'message' => 'Seems like this category is used elsewhere.',
    ]);
});
