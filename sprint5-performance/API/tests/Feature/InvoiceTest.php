<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Download;
use App\Models\Invoice;
use App\Models\User;
use App\Services\InvoiceNumberGenerator;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tests\TestCase;

uses(DatabaseMigrations::class);

//covers(InvoiceController::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->customer = User::factory()->create(['role' => 'user']);
    $this->invoice = Invoice::factory()->create(['user_id' => $this->customer->id]);

    // Mock the Storage facade
    Storage::fake('local');
});

test('admin user can retrieve all invoices', function () {
    Invoice::factory()->count(10)->create();

    // Make a GET request to the endpoint
    $response = $this->getJson('/invoices', $this->headers($this->admin));

    // Assert the correct status and structure of response
    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJsonStructure([
        'current_page',
        'data',
    ]);
});

test('regular user can retrieve only their invoices', function () {
    Invoice::factory()->count(5)->create(['user_id' => $this->customer->id]);
    Invoice::factory()->count(5)->create();

    // Make a GET request to the endpoint
    $response = $this->getJson('/invoices', $this->headers($this->customer));

    // Assert the correct status and response structure
    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJsonCount(6, 'data');
    // Assuming 'data' contains the invoices
});

test('unauthenticated user cannot retrieve invoices', function () {
    // Make a GET request to the endpoint without authentication
    $response = $this->getJson('/invoices');

    // Assert the unauthorized status
    $response->assertStatus(ResponseAlias::HTTP_UNAUTHORIZED);
});

test('it creates new invoice successfully bnpl', function () {
    $paymentDetails = [
        'monthly_installments' => '6'
    ];
    createsNewInvoiceSuccessfully($this, 'buy-now-pay-later', $paymentDetails);
});

test('it creates new invoice successfully gift card', function () {
    $paymentDetails = [
        'gift_card_number' => '1234567890123456',
        'validation_code' => '1234'
    ];
    createsNewInvoiceSuccessfully($this, 'gift-card', $paymentDetails);
});

test('it creates new invoice successfully credit card', function () {
    $paymentDetails = [
        'credit_card_number' => '1234-5678-9101-1121',
        'expiration_date' => '12/2030',
        'cvv' => '123',
        'card_holder_name' => 'John Doe'
    ];
    createsNewInvoiceSuccessfully($this, 'credit-card', $paymentDetails);
});

test('it creates new invoice successfully cash', function () {
    $paymentDetails = [
    ];
    createsNewInvoiceSuccessfully($this, 'cash-on-delivery', []);
});

test('it creates new invoice successfully bank transfer', function () {
    $this->app['env'] = 'local';

    $paymentDetails = [
        'bank_name' => 'Test Bank',
        'account_name' => 'John Doe',
        'account_number' => '123456789'
    ];
    createsNewInvoiceSuccessfully($this, 'bank-transfer', $paymentDetails);
});

test('admin can retrieve any invoice', function () {
    $response = $this->getJson('/invoices/' . $this->invoice->id, $this->headers($this->admin));

    $response->assertStatus(200);
    $response->assertJson(['id' => $this->invoice->id]);
});

test('user can retrieve their own invoice', function () {
    $response = $this->getJson('/invoices/' . $this->invoice->id, $this->headers($this->customer));

    $response->assertStatus(200);
    $response->assertJson(['id' => $this->invoice->id]);
});

test('unauthenticated user cannot retrieve invoice', function () {
    $response = $this->getJson('/invoices/' . $this->invoice->id);

    $response->assertStatus(ResponseAlias::HTTP_UNAUTHORIZED);
});

test('returns not found for nonexistent invoice', function () {
    $response = $this->getJson('/invoices/99999', $this->headers($this->customer));

    $response->assertStatus(ResponseAlias::HTTP_NOT_FOUND);
});

test('it downloads the invoice pdf successfully', function () {
    $invoiceNumber = 'INV-12345';

    // Create a fake file for testing
    Storage::disk('local')->put("invoices/{$invoiceNumber}.pdf", 'Dummy content');

    $response = $this->get("/invoices/{$invoiceNumber}/download-pdf", $this->headers($this->customer));

    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertHeader('Content-Disposition', 'attachment; filename=' . $invoiceNumber . '.pdf');
});

test('it returns not found if pdf does not exist', function () {
    $invoiceNumber = 'INV-12345';

    $response = $this->get("/invoices/{$invoiceNumber}/download-pdf", $this->headers($this->customer));

    $response->assertStatus(ResponseAlias::HTTP_NOT_FOUND);
    $response->assertJson(['message' => 'Document not created. Try again later.']);
});

test('it retrieves status for existing invoice', function () {
    $invoiceNumber = 'INV-12345';
    $download = Download::create([
        'name' => $invoiceNumber,
        'status' => 'COMPLETED',
        'type' => 'INVOICE'
    ]);

    $response = $this->get("/invoices/{$invoiceNumber}/download-pdf-status", $this->headers($this->customer));

    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertJson(['status' => 'COMPLETED']);
});

test('it returns not initiated for nonexistent invoice', function () {
    $invoiceNumber = 'INV-12345';

    $response = $this->get("/invoices/{$invoiceNumber}/download-pdf-status", $this->headers($this->customer));

    $response->assertStatus(ResponseAlias::HTTP_BAD_REQUEST);
    $response->assertJson(['status' => 'NOT_INITIATED']);
});

test('it returns unauthorized if user is not authenticated', function () {
    $invoiceNumber = 'INV-12345';

    $response = $this->get("/invoices/{$invoiceNumber}/download-pdf");

    $response->assertStatus(ResponseAlias::HTTP_UNAUTHORIZED);
});

test('it updates invoice status successfully', function () {
    $payload = [
        'status' => 'COMPLETED',
        'status_message' => 'Order completed successfully'
    ];

    $response = $this->putJson("/invoices/{$this->invoice->id}/status", $payload, $this->headers($this->customer));

    $response->assertStatus(ResponseAlias::HTTP_OK);
    $response->assertExactJson(['success' => true]);
    $this->assertDatabaseHas('invoices', [
        'id' => $this->invoice->id,
        'status' => 'COMPLETED',
        'status_message' => 'Order completed successfully'
    ]);
});

test('it returns validation error for invalid status', function () {
    $payload = [
        'status' => 'INVALID_STATUS',
        'status_message' => 'Invalid status update'
    ];

    $response = $this->putJson("/invoices/{$this->invoice->id}/status", $payload, $this->headers($this->customer));

    $response->assertStatus(ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
});

test('partial update invoice', function () {
    $payload = [
        'billing_street' => 'new street'
    ];

    $response = $this->patchJson("/invoices/{$this->invoice->id}", $payload, $this->headers($this->customer));

    $response->assertStatus(ResponseAlias::HTTP_OK)
        ->assertExactJson([
            'success' => true,
        ]);

    $this->assertDatabaseHas('invoices', [
        'id' => $this->invoice->id,
        'billing_street' => 'new street'
    ]);
});

test('it returns not found for nonexistent invoice', function () {
    $response = $this->putJson("/invoices/99999/status", ['status' => 'COMPLETED'], $this->headers($this->customer));

    $response->assertStatus(ResponseAlias::HTTP_NOT_FOUND);
});

test('regular user can search invoices', function () {
    $response = $this->get("/invoices/search?q={$this->invoice->invoice_number}", $this->headers($this->customer));
    $response->assertStatus(ResponseAlias::HTTP_OK);
});

test('admin user can search invoices', function () {
    $response = $this->get("/invoices/search?q={$this->invoice->invoice_number}", $this->headers($this->admin));
    $response->assertStatus(ResponseAlias::HTTP_OK);
});

/**
 * @param TestCase $test
 * @param string $paymentMethod
 * @param array $paymentDetails
 */
function createsNewInvoiceSuccessfully(TestCase $testCase, string $paymentMethod, array $paymentDetails): void
{
    $invoiceNumberGeneratorMock = Mockery::mock(InvoiceNumberGenerator::class);
    $invoiceNumberGeneratorMock->shouldReceive('generate')
        ->once()
        ->andReturn('INV-' . now()->year . '-12345678');

    app()->instance(InvoiceNumberGenerator::class, $invoiceNumberGeneratorMock);

    $user = User::factory()->create(['role' => 'user']);
    $cart = Cart::factory()->create();
    $product = addProduct();
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'discount_percentage' => 10
    ]);

    $requestData = [
        'cart_id' => $cart->id,
        'payment_method' => $paymentMethod,
        'payment_details' => empty($paymentDetails) ? (object)[] : $paymentDetails,
        'billing_street' => 'address',
        'billing_city' => 'city',
        'billing_country' => 'country',
        'billing_state' => 'state',
        'billing_postal_code' => '12345'
    ];

    $response = $testCase->postJson('/invoices', $requestData, $testCase->headers($user));

    $response->assertStatus(ResponseAlias::HTTP_CREATED);
}
