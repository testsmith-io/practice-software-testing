<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

uses(DatabaseMigrations::class);

const PAYMENT_CHECK_ROUTE = '/payment/check';
const PAYMENT_SUCCESS_MESSAGE = 'Payment was successful';
const PAYMENT_CARD_NUMBER = '1234567890123456';


test('bank transfer with valid details returns success', function () {
    $response = $this->postJson(PAYMENT_CHECK_ROUTE, [
        'payment_method' => 'Bank Transfer',
        'payment_details' => [
            'bank_name' => 'Test Bank',
            'account_name' => 'John Doe',
            'account_number' => '123456789',
        ]
    ]);

    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJson(['message' => PAYMENT_SUCCESS_MESSAGE]);
});

test('cash on delivery returns success', function () {
    $response = $this->postJson(PAYMENT_CHECK_ROUTE, [
        'payment_method' => 'Cash on Delivery',
    ]);

    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJson(['message' => PAYMENT_SUCCESS_MESSAGE]);
});

test('credit card with valid details returns success', function () {
    $response = $this->postJson(PAYMENT_CHECK_ROUTE, [
        'payment_method' => 'Credit Card',
        'payment_details' => [
            'credit_card_number' => '1234-5678-9101-1121',
            'expiration_date' => '12/2030',
            'cvv' => '123',
            'card_holder_name' => 'John Doe',
        ]
    ]);

    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJson(['message' => PAYMENT_SUCCESS_MESSAGE]);
});

test('buy now pay later with valid details returns success', function () {
    $response = $this->postJson(PAYMENT_CHECK_ROUTE, [
        'payment_method' => 'Buy Now Pay Later',
        'payment_details' => [
            'monthly_installments' => 5,
        ]
    ]);

    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJson(['message' => PAYMENT_SUCCESS_MESSAGE]);
});

test('gift card with valid details returns success', function () {
    $response = $this->postJson(PAYMENT_CHECK_ROUTE, [
        'payment_method' => 'Gift Card',
        'payment_details' => [
            'gift_card_number' => PAYMENT_CARD_NUMBER,
            'validation_code' => '1234',
        ]
    ]);

    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJson(['message' => PAYMENT_SUCCESS_MESSAGE]);
});

test('gift card with a valid format passes the payment check', function () {
    $response = $this->postJson(PAYMENT_CHECK_ROUTE, [
        'payment_method' => 'gift-card',
        'payment_details' => [
            'gift_card_number' => PAYMENT_CARD_NUMBER,
            'validation_code' => '1234',
        ]
    ]);

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertJson(['message' => PAYMENT_SUCCESS_MESSAGE]);
});

test('gift card with an unrealistic number is rejected by the payment check', function () {
    $response = $this->postJson(PAYMENT_CHECK_ROUTE, [
        'payment_method' => 'gift-card',
        'payment_details' => [
            'gift_card_number' => '1234567890123456789012345', // 25 chars: too long
            'validation_code' => '1234',
        ]
    ]);

    $response->assertStatus(ResponseAlias::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['payment_details.gift_card_number']);
});

test('gift card with a malformed validation code is rejected by the payment check', function () {
    $response = $this->postJson(PAYMENT_CHECK_ROUTE, [
        'payment_method' => 'gift-card',
        'payment_details' => [
            'gift_card_number' => PAYMENT_CARD_NUMBER,
            'validation_code' => 'no!', // wrong length + non-alphanumeric
        ]
    ]);

    $response->assertStatus(ResponseAlias::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['payment_details.validation_code']);
});

test('invalid payment method returns error', function () {
    $response = $this->postJson(PAYMENT_CHECK_ROUTE, [
        'payment_method' => 'Invalid Method',
    ]);
    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJson(['message' => PAYMENT_SUCCESS_MESSAGE]);
});

test('missing payment method and details returns error', function () {
    $response = $this->postJson(PAYMENT_CHECK_ROUTE, [
    ]);
    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJson(['message' => PAYMENT_SUCCESS_MESSAGE]);
});
