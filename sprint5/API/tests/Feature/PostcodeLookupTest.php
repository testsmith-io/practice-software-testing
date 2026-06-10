<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

use Symfony\Component\HttpFoundation\Response as ResponseAlias;

test('it returns a country-appropriate address for a valid postcode', function () {
    $response = $this->getJson('/postcode-lookup?country=NL&postcode=1011AB&house_number=5');

    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJsonStructure(['street', 'house_number', 'city', 'state', 'country', 'postcode']);
    $response->assertJson(['country' => 'NL']);
});

test('the looked-up city does not depend on the house number', function () {
    $a = $this->getJson('/postcode-lookup?country=NL&postcode=1011AB&house_number=5')->json();
    $b = $this->getJson('/postcode-lookup?country=NL&postcode=1011AB&house_number=99')->json();

    expect($a['city'])->toBe($b['city']);
    expect($a['state'])->toBe($b['state']);
});

test('it rejects a postcode whose format does not fit the selected country', function () {
    // Dutch-format postcode while Austria is selected.
    $response = $this->getJson('/postcode-lookup?country=AT&postcode=1011AB&house_number=5');

    $response->assertStatus(ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
});
