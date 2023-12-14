<?php

namespace tests\Feature;

use App\Models\ProductImage;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tests\TestCase;

class PaymentTest extends TestCase {
    use DatabaseMigrations;

    public function testCheckPayment() {
        $payload = [
            'method' => 'CC',
            'account_name' => 'John Doe',
            'account_number' => '09876543XX'
        ];

        $response = $this->postJson('/payment/check', $payload);

        $response
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonStructure([
                'message',
            ]);
    }

}
