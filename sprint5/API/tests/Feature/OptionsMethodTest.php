<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

uses(DatabaseMigrations::class);

// A direct OPTIONS request (no Origin header, so it bypasses the CORS
// preflight middleware entirely) must still be routed and answered with
// a 204 plus an Allow header listing the methods that path supports.

test('options on a collection endpoint lists its methods', function () {
    $response = $this->call('OPTIONS', '/products');

    $response->assertNoContent();
    expect($response->headers->get('Allow'))
        ->toContain('GET')
        ->toContain('POST')
        ->toContain('QUERY');
});

test('options on a resource endpoint lists its methods', function () {
    $product = $this->addProduct();

    $response = $this->call('OPTIONS', "/products/{$product->id}");

    $response->assertNoContent();
    expect($response->headers->get('Allow'))
        ->toContain('GET')
        ->toContain('PUT')
        ->toContain('PATCH')
        ->toContain('DELETE');
});

test('options does not include HEAD or OPTIONS itself in the Allow header', function () {
    $response = $this->call('OPTIONS', '/products');

    $allowed = array_map('trim', explode(',', $response->headers->get('Allow')));

    expect($allowed)
        ->not->toContain('HEAD')
        ->not->toContain('OPTIONS');
});

test('options on a path with differently named route parameters still resolves', function () {
    // /carts/{id} (GET) and /carts/{cartId} (DELETE) are the same path shape
    // with different parameter names — the Allow header must merge both.
    $response = $this->call('OPTIONS', '/carts/1');

    $response->assertNoContent();
    expect($response->headers->get('Allow'))
        ->toContain('GET')
        ->toContain('DELETE');
});
