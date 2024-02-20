<?php

namespace tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Tests\TestCase;

class ReportTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'password' => bcrypt($password = 'welcome01'),
            'role' => 'admin'
        ]);
    }
    public function testItReturnsTotalSalesPerCountry()
    {
        // Arrange: Create test invoices
        Invoice::factory()->create([
            'total' => 100.00,
            'billing_country' => 'The Netherlands'
        ]);

        Invoice::factory()->create([
            'total' => 150.00,
            'billing_country' => 'The Netherlands'
        ]);

        Invoice::factory()->create([
            'total' => 200.00,
            'billing_country' => 'USA'
        ]);

        // Act: Call the totalSalesPerCountry method
        $response = $this->getJson('/reports/total-sales-per-country', $this->headers($this->admin));

        // Assert: Check if the response is as expected
        $response->assertStatus(200);
        $response->assertJsonFragment(
            ['billing_country' => 'The Netherlands', 'total_sales' => 250.00]
        );
        $response->assertJsonFragment(
            ['billing_country' => 'USA', 'total_sales' => 200.00]
        );
    }

}
