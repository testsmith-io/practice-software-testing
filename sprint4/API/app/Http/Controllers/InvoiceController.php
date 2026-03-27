<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invoice\StoreInvoice;
use App\Mail\Checkout;
use App\Models\Invoice;
use App\Models\Product;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class InvoiceController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:users');
    }

    /**
     * @OA\Get(
     *      path="/invoices",
     *      operationId="getInvoices",
     *      tags={"Invoice"},
     *      summary="Retrieve all invoices",
     *      description="`user` retrieves only related invoices",
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
        return $this->preferredFormat(Invoice::with('invoicelines', 'invoicelines.product')->where('user_id', Auth::user()->id)->orderBy('invoice_date', 'DESC')->filter()->paginate());
    }

    /**
     * @OA\Post(
     *      path="/invoices",
     *      operationId="storeInvoice",
     *      tags={"Invoice"},
     *      summary="Store new invoice",
     *      description="Store new invoice",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Invoice request object",
     *          @OA\JsonContent(ref="#/components/schemas/InvoiceRequest")
     *      ),
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
        $input = $request->except(['invoicelines']);
        $input['invoice_date'] = date('Y-m-d H-i-s');
        $input['invoice_number'] = IdGenerator::generate(['table' => 'invoices', 'field' => 'invoice_number', 'length' => 14, 'prefix' => 'INV-' . date('Y')]);
        $invoice = Invoice::create($input);

        $invoice->invoicelines()->createMany($request->only(['invoicelines'])['invoicelines']);


        $items = [];
        $total = 0;
        foreach ($request->only(['invoicelines'])['invoicelines'] as $invoiceItem) {
            $item['quantity'] = $invoiceItem['quantity'];
            $item['name'] = Product::findOrFail($invoiceItem['product_id'])->name;
            $item['is_rental'] = Product::findOrFail($invoiceItem['product_id'])->is_rental;
            $item['price'] = $invoiceItem['unit_price'];
            $itemTotal = $invoiceItem['quantity'] * $invoiceItem['unit_price'];
            $item['total'] = $itemTotal;
            $total += $itemTotal;
            $items[] = $item;
        }

        if (App::environment('local')) {
            $user = Auth::user();
            Mail::to([$user->email])->send(new Checkout($user->first_name . ' ' . $user->last_name, $items, $total,));
        }

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
     *          @OA\Schema(type="integer")
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
        return $this->preferredFormat(Invoice::with('invoicelines', 'invoicelines.product')->where('id', $id)->where('user_id', Auth::user()->id)->first());
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
     *          @OA\Schema(type="integer")
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
     *      ),
     *      @OA\Response(response="200", ref="#/components/responses/UpdateResponse"),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function updateStatus($id, Request $request)
    {
        $request->validate([
            'status' => Rule::in("AWAITING_FULFILLMENT", "ON_HOLD", "AWAITING_SHIPMENT", "SHIPPED", "COMPLETED"),
            'status_message' => 'string|between:5,50|nullable'
        ]);

        return $this->preferredFormat(['success' => (bool)Invoice::where('id', $id)->update(array('status' => $request['status'], 'status_message' => $request['status_message']))]);
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

        return $this->preferredFormat(Invoice::with('invoicelines', 'invoicelines.product')->where('user_id', Auth::user()->id)->orWhere('invoice_number', 'like', "%$q%")->orWhere('billing_address', 'like', "%$q%")->orWhere('status', 'like', "%$q%")->orderBy('invoice_date', 'DESC')->paginate());
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
     *          @OA\Schema(type="integer")
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
        $invoice = Invoice::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$invoice) {
            return $this->preferredFormat(['success' => false, 'message' => 'Invoice not found'], ResponseAlias::HTTP_NOT_FOUND);
        }

        $updated = (bool)$invoice->update($request->except(['invoicelines']));

        if ($updated && $request->has('invoicelines')) {
            $invoice->invoicelines()->delete();  // Clear old invoice lines
            $invoice->invoicelines()->createMany($request->only('invoicelines')['invoicelines']);
        }

        return $this->preferredFormat(['success' => $updated], ResponseAlias::HTTP_OK);
    }

}
