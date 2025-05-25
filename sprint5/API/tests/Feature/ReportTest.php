<?php

use App\Models\Invoice;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

//covers(ReportController::class);

beforeEach(function () {
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
});

test('it returns total sales per country', function () {
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
});

test('total sales per country', function () {
    $response = $this->get('/reports/total-sales-per-country', $this->headers($this->admin));
    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJsonStructure([
        '*' => ['billing_country', 'total_sales']
    ]);
});

test('top10 purchased products', function () {
    $response = $this->get('/reports/top10-purchased-products', $this->headers($this->admin));
    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJsonStructure([
        '*' => ['name', 'count']
    ]);
});

test('top10 best selling categories', function () {
    $response = $this->get('/reports/top10-best-selling-categories', $this->headers($this->admin));
    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJsonStructure([
        '*' => ['category_name', 'total_earned']
    ]);
});

test('total sales of years', function () {
    $response = $this->get('/reports/total-sales-of-years', $this->headers($this->admin));
    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJsonStructure([
        '*' => ['year', 'total']
    ]);
});

test('average sales per month', function () {
    $response = $this->get('/reports/average-sales-per-month', $this->headers($this->admin));
    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJsonStructure([
        '*' => ['month', 'average', 'amount']
    ]);
});

test('average sales per week', function () {
    $response = $this->get('/reports/average-sales-per-week', $this->headers($this->admin));
    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJsonStructure([
        '*' => ['week', 'average', 'amount']
    ]);
});

test('customers by country', function () {
    $response = $this->get('/reports/customers-by-country', $this->headers($this->admin));
    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJsonStructure([
        '*' => ['amount', 'country']
    ]);
});
