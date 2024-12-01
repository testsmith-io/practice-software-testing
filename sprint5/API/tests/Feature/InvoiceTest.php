<?php

namespace tests\Feature;

use App\Models\Brand;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Download;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Services\InvoiceNumberGenerator;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tests\TestCase;

class InvoiceTest extends TestCase {
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->customer = User::factory()->create(['role' => 'user']);
        $this->invoice = Invoice::factory()->create(['user_id' => $this->customer->id]);

        // Mock the Storage facade
        Storage::fake('local');
    }

    public function testAdminUserCanRetrieveAllInvoices()
    {
        Invoice::factory()->count(10)->create();

        // Make a GET request to the endpoint
        $response = $this->getJson('/invoices', $this->headers($this->admin));

        // Assert the correct status and structure of response
        $response->assertStatus(ResponseAlias::HTTP_OK);
        $response->assertJsonStructure([
            'current_page',
            'data',
        ]);
    }

    public function testRegularUserCanRetrieveOnlyTheirInvoices()
    {
        Invoice::factory()->count(5)->create(['user_id' => $this->customer->id]);
        Invoice::factory()->count(5)->create();

        // Make a GET request to the endpoint
        $response = $this->getJson('/invoices', $this->headers($this->customer));

        // Assert the correct status and response structure
        $response->assertStatus(ResponseAlias::HTTP_OK);
        $response->assertJsonCount(6, 'data'); // Assuming 'data' contains the invoices
    }

    public function testUnauthenticatedUserCannotRetrieveInvoices()
    {
        // Make a GET request to the endpoint without authentication
        $response = $this->getJson('/invoices');

        // Assert the unauthorized status
        $response->assertStatus(ResponseAlias::HTTP_UNAUTHORIZED);
    }

    public function testItCreatesNewInvoiceSuccessfullyBnpl() {
        $paymentDetails = [
            'monthly_installments' => '6'
        ];
        $this->testItCreatesNewInvoiceSuccessfully('buy-now-pay-later', $paymentDetails);
    }

    public function testItCreatesNewInvoiceSuccessfullyGiftCard() {
        $paymentDetails = [
            'gift_card_number' => '1234567890123456',
            'validation_code' => '1234'
        ];
        $this->testItCreatesNewInvoiceSuccessfully('gift-card', $paymentDetails);
    }

    public function testItCreatesNewInvoiceSuccessfullyCreditCard() {
        $paymentDetails = [
            'credit_card_number' => '1234-5678-9101-1121',
            'expiration_date' => '12/2030',
            'cvv' => '123',
            'card_holder_name' => 'John Doe'
        ];
        $this->testItCreatesNewInvoiceSuccessfully('credit-card', $paymentDetails);
    }

    public function testItCreatesNewInvoiceSuccessfullyCash() {
        $paymentDetails = [
        ];
        $this->testItCreatesNewInvoiceSuccessfully('cash-on-delivery', $paymentDetails);
    }

    public function testItCreatesNewInvoiceSuccessfullyBankTransfer() {
        $this->app['env'] = 'local';

        $paymentDetails = [
            'bank_name' => 'Test Bank',
            'account_name' => 'John Doe',
            'account_number' => '123456789'
        ];
        $this->testItCreatesNewInvoiceSuccessfully('bank-transfer', $paymentDetails);
    }

    public function testAdminCanRetrieveAnyInvoice()
    {
        $response = $this->getJson('/invoices/' . $this->invoice->id, $this->headers($this->admin));

        $response->assertStatus(200);
        $response->assertJson(['id' => $this->invoice->id]);
    }

    public function testUserCanRetrieveTheirOwnInvoice()
    {
        $response = $this->getJson('/invoices/' . $this->invoice->id, $this->headers($this->customer));

        $response->assertStatus(200);
        $response->assertJson(['id' => $this->invoice->id]);
    }

    public function testUnauthenticatedUserCannotRetrieveInvoice()
    {
        $response = $this->getJson('/invoices/' . $this->invoice->id);

        $response->assertStatus(ResponseAlias::HTTP_UNAUTHORIZED);
    }

    public function testReturnsNotFoundForNonexistentInvoice()
    {
        $response = $this->getJson('/invoices/99999', $this->headers($this->customer));

        $response->assertStatus(ResponseAlias::HTTP_NOT_FOUND);
    }

    public function testItDownloadsTheInvoicePdfSuccessfully()
    {
        $invoiceNumber = 'INV-12345';

        // Create a fake file for testing
        Storage::disk('local')->put("invoices/{$invoiceNumber}.pdf", 'Dummy content');

        $response = $this->get("/invoices/{$invoiceNumber}/download-pdf", $this->headers($this->customer));

        $response->assertStatus(ResponseAlias::HTTP_OK);
        $response->assertHeader('Content-Disposition', 'attachment; filename='.$invoiceNumber.'.pdf');
    }

    public function testItReturnsNotFoundIfPdfDoesNotExist()
    {
        $invoiceNumber = 'INV-12345';

        $response = $this->get("/invoices/{$invoiceNumber}/download-pdf", $this->headers($this->customer));

        $response->assertStatus(ResponseAlias::HTTP_NOT_FOUND);
        $response->assertJson(['message' => 'Document not created. Try again later.']);
    }

    public function testItRetrievesStatusForExistingInvoice()
    {
        $invoiceNumber = 'INV-12345';
        $download = Download::create([
            'name' => $invoiceNumber,
            'status' => 'COMPLETED',
            'type' => 'INVOICE'
        ]);

        $response = $this->get("/invoices/{$invoiceNumber}/download-pdf-status", $this->headers($this->customer));

        $response->assertStatus(ResponseAlias::HTTP_OK);
        $response->assertJson(['status' => 'COMPLETED']);
    }

    public function testItReturnsNotInitiatedForNonexistentInvoice()
    {
        $invoiceNumber = 'INV-12345';

        $response = $this->get("/invoices/{$invoiceNumber}/download-pdf-status", $this->headers($this->customer));

        $response->assertStatus(ResponseAlias::HTTP_BAD_REQUEST);
        $response->assertJson(['status' => 'NOT_INITIATED']);
    }

    public function testItReturnsUnauthorizedIfUserIsNotAuthenticated()
    {
        $invoiceNumber = 'INV-12345';

        $response = $this->get("/invoices/{$invoiceNumber}/download-pdf");

        $response->assertStatus(ResponseAlias::HTTP_UNAUTHORIZED);
    }

    public function testItUpdatesInvoiceStatusSuccessfully()
    {
        $payload = [
            'status' => 'COMPLETED',
            'status_message' => 'Order completed successfully'
        ];

        $response = $this->putJson("/invoices/{$this->invoice->id}/status", $payload, $this->headers($this->customer));

        $response->assertStatus(ResponseAlias::HTTP_OK);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('invoices', [
            'id' => $this->invoice->id,
            'status' => 'COMPLETED',
            'status_message' => 'Order completed successfully'
        ]);
    }

    public function testItReturnsValidationErrorForInvalidStatus()
    {
        $payload = [
            'status' => 'INVALID_STATUS',
            'status_message' => 'Invalid status update'
        ];

        $response = $this->putJson("/invoices/{$this->invoice->id}/status", $payload, $this->headers($this->customer));

        $response->assertStatus(ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testPartialUpdateInvoice() {
        $payload = [
            'billing_address' => 'new street'
        ];

        $response = $this->patchJson("/invoices/{$this->invoice->id}", $payload, $this->headers($this->customer));

        $response->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $this->invoice->id,
            'billing_address' => 'new street'
        ]);
    }

    public function testItReturnsNotFoundForNonexistentInvoice()
    {
        $response = $this->putJson("/invoices/99999/status", ['status' => 'COMPLETED'], $this->headers($this->customer));

        $response->assertStatus(ResponseAlias::HTTP_NOT_FOUND);
    }

    public function testRegularUserCanSearchInvoices()
    {
        $response = $this->get("/invoices/search?q={$this->invoice->invoice_number}", $this->headers($this->customer));
        $response->assertStatus(ResponseAlias::HTTP_OK);
    }

    public function testAdminUserCanSearchInvoices()
    {
        $response = $this->get("/invoices/search?q={$this->invoice->invoice_number}", $this->headers($this->admin));
        $response->assertStatus(ResponseAlias::HTTP_OK);
    }


    private function testItCreatesNewInvoiceSuccessfully($paymentMethod, $paymentDetails)
    {
        $invoiceNumberGeneratorMock = Mockery::mock(InvoiceNumberGenerator::class);
        $invoiceNumberGeneratorMock->shouldReceive('generate')
            ->once()
            ->andReturn('INV-' . now()->year . '-12345678');

        $this->app->instance(InvoiceNumberGenerator::class, $invoiceNumberGeneratorMock);

        $user = User::factory()->create(['role' => 'user']);
        $cart = Cart::factory()->create();
        $product = $this->addProduct();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'discount_percentage' => 10
        ]);

        $requestData = [
            'cart_id' => $cart->id,
            'payment_method' => $paymentMethod,
            'payment_details' => $paymentDetails,
            'billing_address' => 'address',
            'billing_city' => 'city',
            'billing_country' => 'country',
            'billing_state' => 'state',
            'billing_postcode' => '12345'
        ];

        $response = $this->postJson('/invoices', $requestData, $this->headers($user));

        $response->assertStatus(ResponseAlias::HTTP_CREATED);
    }

}
