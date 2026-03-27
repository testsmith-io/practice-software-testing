<?php

namespace tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
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
    }
    public function testItReturnsTotalSalesPerCountry()
    {
        // Act: Call the totalSalesPerCountry method
        $response = $this->getJson('/reports/total-sales-per-country', $this->headers($this->admin));

        // Assert: Check if the response is as expected
        $response->assertStatus(ResponseAlias::HTTP_OK);
        $response->assertJsonFragment(
            ['billing_country' => 'The Netherlands', 'total_sales' => 250.00]
        );
        $response->assertJsonFragment(
            ['billing_country' => 'USA', 'total_sales' => 200.00]
        );
    }

    public function testTotalSalesPerCountry()
    {
        $response = $this->get('/reports/total-sales-per-country', $this->headers($this->admin));
        $response->assertStatus(ResponseAlias::HTTP_OK);
        $response->assertJsonStructure([
            '*' => ['billing_country', 'total_sales']
        ]);
    }

    public function testTop10PurchasedProducts()
    {
        $response = $this->get('/reports/top10-purchased-products', $this->headers($this->admin));
        $response->assertStatus(ResponseAlias::HTTP_OK);
        $response->assertJsonStructure([
            '*' => ['name', 'count']
        ]);
    }

    public function testTop10BestSellingCategories()
    {
        $response = $this->get('/reports/top10-best-selling-categories', $this->headers($this->admin));
        $response->assertStatus(ResponseAlias::HTTP_OK);
        $response->assertJsonStructure([
            '*' => ['category_name', 'total_earned']
        ]);
    }

    public function testTotalSalesOfYears()
    {
        $response = $this->get('/reports/total-sales-of-years', $this->headers($this->admin));
        $response->assertStatus(ResponseAlias::HTTP_OK);
        $response->assertJsonStructure([
            '*' => ['year', 'total']
        ]);
    }

    public function testAverageSalesPerMonth()
    {
        $response = $this->get('/reports/average-sales-per-month', $this->headers($this->admin));
        $response->assertStatus(ResponseAlias::HTTP_OK);
        $response->assertJsonStructure([
            '*' => ['month', 'average', 'amount']
        ]);
    }

    public function testAverageSalesPerWeek()
    {
        $response = $this->get('/reports/average-sales-per-week', $this->headers($this->admin));
        $response->assertStatus(ResponseAlias::HTTP_OK);
        $response->assertJsonStructure([
            '*' => ['week', 'average', 'amount']
        ]);
    }

    public function testCustomersByCountry()
    {
        $response = $this->get('/reports/customers-by-country', $this->headers($this->admin));
        $response->assertStatus(ResponseAlias::HTTP_OK);
        $response->assertJsonStructure([
            '*' => ['amount', 'country']
        ]);
    }

}
