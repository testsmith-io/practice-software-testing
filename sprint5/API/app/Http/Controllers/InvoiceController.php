<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invoice\DestroyInvoice;
use App\Http\Requests\Invoice\PatchInvoice;
use App\Http\Requests\Invoice\StoreInvoice;
use App\Jobs\SendCheckoutEmail;
use App\Jobs\UpdateProductInventory;
use App\Models\PaymentBankTransferDetails;
use App\Models\Cart;
use App\Models\Download;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentBnplDetails;
use App\Models\PaymentCashOnDeliveryDetails;
use App\Models\PaymentCreditCardDetails;
use App\Models\PaymentGiftCardDetails;
use App\Rules\SubscriptSuperscriptRule;
use App\Services\InvoiceService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class InvoiceController extends Controller
{

    private $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
        $this->middleware('auth:users');
    }

    /**
     * @OA\Get(
     *      path="/invoices",
     *      operationId="getInvoices",
     *      tags={"Invoice"},
     *      summary="Retrieve all invoices",
     *      description="`admin` retrieves all invoices, `user` retrieves only related invoices",
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="pagenumber",
     *          required=false,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              title="PaginatedInvoiceResponse",
     *              @OA\Property(property="current_page", type="integer", example=1),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/InvoiceResponse")
     *              ),
     *              @OA\Property(property="from", type="integer", example=1),
     *              @OA\Property(property="last_page", type="integer", example=1),
     *              @OA\Property(property="per_page", type="integer", example=1),
     *              @OA\Property(property="to", type="integer", example=1),
     *              @OA\Property(property="total", type="integer", example=1),
     *          )
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function index()
    {
        $isAdmin = app('auth')->parseToken()->getPayload()->get('role') == "admin";
        return $this->preferredFormat($this->invoiceService->getInvoices($isAdmin));
    }

    /**
     * @OA\Post(
     *      path="/invoices",
     *      operationId="storeInvoice",
     *      tags={"Invoice"},
     *      summary="Store new invoice",
     *      description="Store new invoice",
     *      @OA\RequestBody(
     *           required=true,
     *           description="Invoice request object",
     *           @OA\JsonContent(ref="#/components/schemas/BaseInvoiceRequest")
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/InvoiceResponse")
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function store(StoreInvoice $request)
    {
        $invoice = $this->invoiceService->createInvoice($request->except(['cart_id']), $request->input('cart_id'));
        $this->invoiceService->handlePayment($invoice->id, $request->input('payment_method'), $request->input('payment_details') ?? []);

        return $this->preferredFormat($invoice, ResponseAlias::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *      path="/invoices/{invoiceId}",
     *      operationId="getInvoice",
     *      tags={"Invoice"},
     *      summary="Retrieve specific invoice",
     *      description="Retrieve specific invoice",
     *      @OA\Parameter(
     *          name="invoiceId",
     *          in="path",
     *          example=1,
     *          description="The invoiceId parameter in path",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/InvoiceResponse")
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function show($id)
    {
        $isAdmin = app('auth')->parseToken()->getPayload()->get('role') == "admin";
        return $this->preferredFormat($this->invoiceService->getInvoice($id, $isAdmin));
    }

    /**
     * @OA\Get(
     *      path="/invoices/{invoice_number}/download-pdf",
     *      operationId="downloadPDF",
     *      tags={"Invoice"},
     *      summary="Download already generated PDF of a specific invoice",
     *      description="Download already generated PDF of a specific invoice",
     *      @OA\Parameter(
     *          name="invoice_number",
     *          in="path",
     *          example=1,
     *          description="The invoice_number parameter in path",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/InvoiceResponse")
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function downloadPDF($invoiceNumber)
    {
        $file = $this->invoiceService->downloadPDF($invoiceNumber);
        if ($file) {
            return $file;
        }
        return $this->preferredFormat(['message' => 'Document not created. Try again later.'], ResponseAlias::HTTP_NOT_FOUND);
    }

    /**
     * @OA\Get(
     *      path="/invoices/{invoice_number}/download-pdf-status",
     *      operationId="downloadPDFStatus",
     *      tags={"Invoice"},
     *      summary="Retrieve the status of the PDF.",
     *      description="Retrieve the status of the PDF. The status can be INITIATED, IN_PROGRESS, COMPLETED",
     *      @OA\Parameter(
     *          name="invoice_number",
     *          in="path",
     *          example=1,
     *          description="The invoice_number parameter in path",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/InvoiceResponse")
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function downloadPDFStatus($invoiceNumber)
    {
        $status = $this->invoiceService->getPDFStatus($invoiceNumber);

        if ($status['status'] === 'NOT_INITIATED') {
            return $this->preferredFormat($status, ResponseAlias::HTTP_BAD_REQUEST);
        }

        return $this->preferredFormat($status, ResponseAlias::HTTP_OK);
    }

    /**
     * @OA\Put(
     *      path="/invoices/{invoiceId}/status",
     *      operationId="updateInvoiceStatus",
     *      tags={"Invoice"},
     *      summary="Update invoice status",
     *      description="Update invoice status",
     *      @OA\Parameter(
     *          name="invoiceId",
     *          in="path",
     *          description="The invoiceId parameter in path",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *                  mediaType="application/json",
     *             @OA\Schema(
     *                 title="InvoiceStatusRequest",
     *                 @OA\Property(
     *                    property="status",
     *                    type="string",
     *                    enum={"AWAITING_FULFILLMENT", "ON_HOLD", "AWAITING_SHIPMENT", "SHIPPED", "COMPLETED"},
     *                    description="The status of the order"
     *                 ),
     *                 @OA\Property(
     *                    property="status_message",
     *                    type="string",
     *                    description="A message describing the status",
     *                    nullable=true,
     *                    minLength=5,
     *                    maxLength=50
     *                 ),
     *               )
     *           )
     *       ),
     *       @OA\Response(response="200", ref="#/components/responses/UpdateResponse"),
     *       @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *       @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *       @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *       @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     *       security={{ "apiAuth": {} }}
     * )
     */
    public function updateStatus($id, Request $request)
    {
        $request->validate([
            'status' => Rule::in("AWAITING_FULFILLMENT", "ON_HOLD", "AWAITING_SHIPMENT", "SHIPPED", "COMPLETED"),
            'status_message' => ['string', 'between:5,50', 'nullable'],
        ]);

        $updated = $this->invoiceService->updateInvoiceStatus($id, $request->all());

        return $updated
            ? $this->preferredFormat(['success' => true], ResponseAlias::HTTP_OK)
            : $this->preferredFormat(['message' => 'Invoice not found'], ResponseAlias::HTTP_NOT_FOUND);
    }

    /**
     * @OA\Get(
     *      path="/invoices/search",
     *      operationId="searchInvoice",
     *      tags={"Invoice"},
     *      summary="Retrieve specific invoices matching the search query",
     *      description="Search is performed on the `invoice_number`, `billing_address` and `status` column",
     *      @OA\Parameter(
     *          name="q",
     *          in="query",
     *          description="A query phrase",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="pagenumber",
     *          required=false,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              title="PaginatedInvoiceResponse",
     *              @OA\Property(property="current_page", type="integer", example=1),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/InvoiceResponse")
     *              ),
     *              @OA\Property(property="from", type="integer", example=1),
     *              @OA\Property(property="last_page", type="integer", example=1),
     *              @OA\Property(property="per_page", type="integer", example=1),
     *              @OA\Property(property="to", type="integer", example=1),
     *              @OA\Property(property="total", type="integer", example=1),
     *          )
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function search(Request $request)
    {
        $q = $request->get('q');
        $isAdmin = app('auth')->parseToken()->getPayload()->get('role') == "admin";

        return $this->preferredFormat($this->invoiceService->searchInvoices($q, $isAdmin));
    }

    /**
     * @OA\Put(
     *      path="/invoices/{invoiceId}",
     *      operationId="updateInvoice",
     *      tags={"Invoice"},
     *      summary="Update specific invoice",
     *      description="Update specific invoice",
     *      @OA\Parameter(
     *          name="invoiceId",
     *          in="path",
     *          description="The invoiceId parameter in path",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Invoice request object",
     *          @OA\JsonContent(ref="#/components/schemas/InvoiceRequest")
     *      ),
     *      @OA\Response(response="200", ref="#/components/responses/UpdateResponse"),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function update(StoreInvoice $request, $id)
    {
        $success = $this->invoiceService->updateInvoice($id, $request->all());

        return $this->preferredFormat(['success' => (bool)$success], ResponseAlias::HTTP_OK);
    }

    /**
     * @OA\Patch(
     *      path="/invoices/{invoiceId}",
     *      operationId="patchInvoice",
     *      tags={"Invoice"},
     *      summary="Partially update specific invoice",
     *      description="Partially update specific invoice",
     *      @OA\Parameter(
     *          name="invoiceId",
     *          in="path",
     *          description="The invoiceId parameter in path",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Partial invoice request object. Only fields to be updated should be included.",
     *          @OA\JsonContent(ref="#/components/schemas/InvoiceRequest")
     *      ),
     *      @OA\Response(response="200", ref="#/components/responses/UpdateResponse"),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function patch(PatchInvoice $request, $id)
    {
        $validatedData = $request->validated();
        $success = $this->invoiceService->patchInvoice($id, $validatedData);

        return $this->preferredFormat(['success' => (bool)$success], ResponseAlias::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *      path="/invoices/{invoiceId}",
     *      operationId="deleteInvoice",
     *      tags={"Invoice"},
     *      summary="Delete specific invoice",
     *      description="Admin role is required to delete a specific invoice",
     *      @OA\Parameter(
     *          name="invoiceId",
     *          in="path",
     *          description="The invoiceId parameter in path",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(response=204, description="Successful operation"),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="409", ref="#/components/responses/ConflictResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     *      security={{ "apiAuth": {} }}
     * ),
     */
    public function destroy(DestroyInvoice $request, $id)
    {
        $deleted = $this->invoiceService->deleteInvoice($id);

        return $deleted
            ? $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT)
            : $this->preferredFormat(['message' => 'Unable to delete invoice'], ResponseAlias::HTTP_CONFLICT);
    }
}
