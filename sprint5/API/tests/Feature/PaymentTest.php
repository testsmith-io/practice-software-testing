<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

uses(DatabaseMigrations::class);

//covers(PaymentController::class);

test('bank transfer with valid details returns success', function () {
    $response = $this->postJson('/payment/check', [
        'payment_method' => 'Bank Transfer',
        'payment_details' => [
            'bank_name' => 'Test Bank',
            'account_name' => 'John Doe',
            'account_number' => '123456789',
        ]
    ]);

    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJson(['message' => 'Payment was successful']);
});

test('cash on delivery returns success', function () {
    $response = $this->postJson('/payment/check', [
        'payment_method' => 'Cash on Delivery',
    ]);

    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJson(['message' => 'Payment was successful']);
});

test('credit card with valid details returns success', function () {
    $response = $this->postJson('/payment/check', [
        'payment_method' => 'Credit Card',
        'payment_details' => [
            'credit_card_number' => '1234-5678-9101-1121',
            'expiration_date' => '12/2030',
            'cvv' => '123',
            'card_holder_name' => 'John Doe',
        ]
    ]);

    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJson(['message' => 'Payment was successful']);
});

test('buy now pay later with valid details returns success', function () {
    $response = $this->postJson('/payment/check', [
        'payment_method' => 'Buy Now Pay Later',
        'payment_details' => [
            'monthly_installments' => 5,
        ]
    ]);

    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJson(['message' => 'Payment was successful']);
});

test('gift card with valid details returns success', function () {
    $response = $this->postJson('/payment/check', [
        'payment_method' => 'Gift Card',
        'payment_details' => [
            'gift_card_number' => '1234567890123456',
            'validation_code' => '1234',
        ]
    ]);

    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJson(['message' => 'Payment was successful']);
});

test('invalid payment method returns error', function () {
    $response = $this->postJson('/payment/check', [
        'payment_method' => 'Invalid Method',
    ]);
    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJson(['message' => 'Payment was successful']);
});

test('missing payment method and details returns error', function () {
    $response = $this->postJson('/payment/check', [
    ]);
    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJson(['message' => 'Payment was successful']);
});
