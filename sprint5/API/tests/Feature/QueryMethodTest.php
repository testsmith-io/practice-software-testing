<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

uses(DatabaseMigrations::class);

// The HTTP QUERY method (RFC 10008) carries query criteria in a JSON request
// body and behaves like the equivalent GET with a query string.

test('query products with filter criteria in the body', function () {
    $product = $this->addProduct();

    $response = $this->json('QUERY', '/products', [
        'by_category_slug' => $product->category->slug,
    ]);

    $response
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'name',
                    'description',
                    'price',
                ]
            ]
        ]);
});

test('query products returns the same results as GET with a query string', function () {
    $this->addProduct();

    $getResponse = $this->getJson('/products?is_rental=false');
    $queryResponse = $this->json('QUERY', '/products', ['is_rental' => 'false']);

    $queryResponse->assertStatus(ResponseAlias::HTTP_OK);
    expect($queryResponse->json('data'))->toEqual($getResponse->json('data'));
});

test('query products advertises support through the Accept-Query header', function () {
    $this->addProduct();

    $response = $this->json('QUERY', '/products', []);

    $response
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertHeader('Accept-Query', 'application/json');
});

test('query without a JSON content type is rejected with 415', function () {
    $this->addProduct();

    $response = $this->call('QUERY', '/products', [], [], [], ['CONTENT_TYPE' => 'text/plain'], 'q=hammer');

    $response->assertStatus(ResponseAlias::HTTP_UNSUPPORTED_MEDIA_TYPE);
});

test('query product search by term in the body', function () {
    // Explicit multi-word name: MariaDB full-text search requires every token
    // to be indexed, and faker names can contain tokens below the minimum
    // token size (e.g. "Mr."), which return no results there.
    $product = $this->addProduct();
    $product->update(['name' => 'Cordless Screwdriver']);

    $response = $this->json('QUERY', '/products/search', ['q' => 'Cordless Screwdriver']);

    $response
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonPath('data.0.name', 'Cordless Screwdriver');
});

test('query brand search by term in the body', function () {
    $product = $this->addProduct();
    $product->brand->update(['name' => 'Forgecraft']);

    $response = $this->json('QUERY', '/brands/search', ['q' => 'Forgecraft']);

    $response
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonFragment(['name' => 'Forgecraft']);
});

test('query category search by term in the body', function () {
    $product = $this->addProduct();
    $product->category->update(['name' => 'Workbenches']);

    $response = $this->json('QUERY', '/categories/search', ['q' => 'Workbenches']);

    $response
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonFragment(['name' => 'Workbenches']);
});

test('query categories tree scoped by slug in the body', function () {
    $product = $this->addProduct();

    $response = $this->json('QUERY', '/categories/tree', [
        'by_category_slug' => $product->category->slug,
    ]);

    $response
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonFragment(['slug' => $product->category->slug]);
});

test('admin can query user search by term in the body', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'user', 'first_name' => 'Serenity']);

    $response = $this->json('QUERY', '/users/search', ['q' => 'Serenity'], $this->headers($admin));

    $response
        ->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJsonFragment(['first_name' => 'Serenity']);
});

test('admin can query invoice search by term in the body', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->json('QUERY', '/invoices/search', ['q' => 'INV'], $this->headers($admin));

    $response->assertStatus(ResponseAlias::HTTP_OK);
});
