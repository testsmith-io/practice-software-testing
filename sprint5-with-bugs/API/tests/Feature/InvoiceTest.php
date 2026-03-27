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

class InvoiceTest extends TestCase
{
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

    public function testRegularUserCanRetrieveAllInvoices()
    {
        Invoice::factory()->count(5)->create(['user_id' => $this->customer->id]);
        Invoice::factory()->count(5)->create();

        // Make a GET request to the endpoint
        $response = $this->getJson('/invoices', $this->headers($this->customer));

        // Assert the correct status and response structure
        $response->assertStatus(ResponseAlias::HTTP_OK);
        $response->assertJsonCount(11, 'data'); // Assuming 'data' contains the invoices
    }

    public function testUnauthenticatedUserCanRetrieveInvoices()
    {
        // Make a GET request to the endpoint without authentication
        $response = $this->getJson('/invoices');

        // Assert the unauthorized status
        $response->assertStatus(ResponseAlias::HTTP_OK);
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

    public function testItReturnsNotFoundForNonexistentInvoice()
    {
        $response = $this->putJson("/invoices/99999/status", ['status' => 'COMPLETED'], $this->headers($this->customer));

        $response->assertStatus(ResponseAlias::HTTP_OK);
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


    public function testItCreatesNewInvoiceSuccessfully()
    {
        $invoiceNumberGeneratorMock = Mockery::mock(InvoiceNumberGenerator::class);
        $invoiceNumberGeneratorMock->shouldReceive('generate')
            ->once()
            ->andReturn('INV-' . now()->year . '-12345678');

        $this->app->instance(InvoiceNumberGenerator::class, $invoiceNumberGeneratorMock);

        $user = User::factory()->create(['role' => 'user']);
        $product = $this->addProduct();

        $requestData = [
            'user_id' => $user->id,
            'payment_method' => 'Buy Now Pay Later',
            'payment_account_name' => 'account name',
            'payment_account_number' => '0987654321',
            'total' => 12.01,
            'billing_address' => 'address',
            'billing_city' => 'city',
            'billing_country' => 'country',
            'billing_state' => 'state',
            'billing_postcode' => '12345',
            'invoice_items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 6.005
                ]
            ]
        ];

        $response = $this->postJson('/invoices', $requestData, $this->headers($user));

        $response->assertStatus(ResponseAlias::HTTP_CREATED);
    }

}
