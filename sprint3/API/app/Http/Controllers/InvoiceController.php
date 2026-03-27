<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invoice\DestroyInvoice;
use App\Http\Requests\Invoice\StoreInvoice;
use App\Mail\Checkout;
use App\Models\Invoice;
use App\Models\Product;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class InvoiceController extends Controller
{

    /**
     * @OA\Get(
     *      path="/invoices",
     *      operationId="getInvoices",
     *      tags={"Invoice"},
     *      summary="Retrieve all invoices",
     *      description="Retrieves invoices",
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
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     * )
     */
    public function index()
    {
        return $this->preferredFormat(Invoice::with('invoicelines', 'invoicelines.product')->orderBy('invoice_date', 'DESC')->paginate());
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
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     * )
     */
    public function store(StoreInvoice $request)
    {
        $input = $request->except(['invoice_items']);
        $input['invoice_date'] = date('Y-m-d H-i-s');
        $input['invoice_number'] = IdGenerator::generate(['table' => 'invoices', 'field' => 'invoice_number', 'length' => 14, 'prefix' => 'INV-' . date('Y')]);
        $invoice = Invoice::create($input);

        $invoice->invoicelines()->createMany($request->only(['invoice_items'])['invoice_items']);

        if (App::environment('local')) {
            $items = [];
            $total = 0;
            foreach ($request->only(['invoice_items'])['invoice_items'] as $invoiceItem) {
                $item['quantity'] = $invoiceItem['quantity'];
                $item['name'] = Product::findOrFail($invoiceItem['product_id'])->name;
                $item['is_rental'] = Product::findOrFail($invoiceItem['product_id'])->is_rental;
                $item['price'] = $invoiceItem['unit_price'];
                $itemTotal = $invoiceItem['quantity'] * $invoiceItem['unit_price'];
                $item['total'] = $itemTotal;
                $total += $itemTotal;
                $items[] = $item;
            }

            Mail::to([$input['email']])->send(new Checkout($input['first_name'] . ' ' . $input['last_name'], $items, $total,));
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
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     * )
     */
    public function show($id)
    {
        return $this->preferredFormat(Invoice::with('invoicelines', 'invoicelines.product')->where('id', $id)->first());

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
     *         @OA\MediaType(
     *                 mediaType="application/json",
     *            @OA\Schema(
     *                @OA\Property(
     *                   property="status",
     *                   type="string",
     *                   enum={"AWAITING_FULFILLMENT", "ON_HOLD", "AWAITING_SHIPMENT", "SHIPPED", "COMPLETED"},
     *                   description="The status of the order"
     *                ),
     *                @OA\Property(
     *                   property="status_message",
     *                   type="string",
     *                   description="A message describing the status",
     *                   nullable=true,
     *                   minLength=5,
     *                   maxLength=50
     *                ),
     *              )
     *          )
     *      ),
     *      @OA\Response(response="200", ref="#/components/responses/UpdateResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
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
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     * )
     */
    public function search(Request $request)
    {
        $q = $request->get('q');

        return $this->preferredFormat(Invoice::with('invoicelines', 'invoicelines.product')->where('invoice_number', 'like', "%$q%")->orWhere('billing_address', 'like', "%$q%")->orWhere('status', 'like', "%$q%")->orderBy('invoice_date', 'DESC')->paginate());
    }

    /**
     * @OA\Delete(
     *      path="/invoices/{invoiceId}",
     *      operationId="deleteInvoice",
     *      tags={"Invoice"},
     *      summary="Delete specific invoice",
     *      description="",
     *      @OA\Parameter(
     *          name="invoiceId",
     *          in="path",
     *          description="The invoiceId parameter in path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response=204, description="Successful operation"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="409", ref="#/components/responses/ConflictResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     * ),
     */
    public function destroy(DestroyInvoice $request, $id)
    {
        try {
            Invoice::find($id)->delete();
            return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return $this->preferredFormat([
                    'success' => false,
                    'message' => 'Seems like this invoice is used elsewhere.',
                ], ResponseAlias::HTTP_CONFLICT);
            }
        }
    }
}
