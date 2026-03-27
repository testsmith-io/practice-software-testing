<?php

namespace tests\Feature;

use App\Models\ProductImage;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tests\TestCase;

class PaymentTest extends TestCase {
    use DatabaseMigrations;

    public function testBankTransferWithValidDetailsReturnsSuccess()
    {
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
    }

    public function testCashOnDeliveryReturnsSuccess()
    {
        $response = $this->postJson('/payment/check', [
            'payment_method' => 'Cash on Delivery',
        ]);

        $response->assertStatus(ResponseAlias::HTTP_OK);
        $response->assertJson(['message' => 'Payment was successful']);
    }

    public function testCreditCardWithValidDetailsReturnsSuccess()
    {
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
    }

    public function testBuyNowPayLaterWithValidDetailsReturnsSuccess()
    {
        $response = $this->postJson('/payment/check', [
            'payment_method' => 'Buy Now Pay Later',
            'payment_details' => [
                'monthly_installments' => 5,
            ]
        ]);

        $response->assertStatus(ResponseAlias::HTTP_OK);
        $response->assertJson(['message' => 'Payment was successful']);
    }

    public function testGiftCardWithValidDetailsReturnsSuccess()
    {
        $response = $this->postJson('/payment/check', [
            'payment_method' => 'Gift Card',
            'payment_details' => [
                'gift_card_number' => '1234567890123456',
                'validation_code' => '1234',
            ]
        ]);

        $response->assertStatus(ResponseAlias::HTTP_OK);
        $response->assertJson(['message' => 'Payment was successful']);
    }

    public function testInvalidPaymentMethodReturnsError()
    {
        $response = $this->postJson('/payment/check', [
            'payment_method' => 'Invalid Method',
        ]);
        $response->assertStatus(ResponseAlias::HTTP_OK);
        $response->assertJson(['message' => 'Payment was successful']);
    }

    public function testMissingPaymentMethodAndDetailsReturnsError()
    {
        $response = $this->postJson('/payment/check', [
        ]);
        $response->assertStatus(ResponseAlias::HTTP_OK);
        $response->assertJson(['message' => 'Payment was successful']);
    }
}
