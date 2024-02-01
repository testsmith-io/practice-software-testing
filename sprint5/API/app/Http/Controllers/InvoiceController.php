<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invoice\DestroyInvoice;
use App\Http\Requests\Invoice\StoreInvoice;
use App\Jobs\SendCheckoutEmail;
use App\Jobs\UpdateProductInventory;
use App\Mail\Checkout;
use App\Models\PaymentBankTransferDetails;
use App\Models\Cart;
use App\Models\Download;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentBnplDetails;
use App\Models\PaymentCashOnDeliveryDetails;
use App\Models\PaymentCreditCardDetails;
use App\Models\PaymentGiftCardDetails;
use App\Models\Product;
use App\Rules\SubscriptSuperscriptRule;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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
     *      description="`admin` retrieves all invoices, `user` retrieves only related invoices",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="current_page", type="integer", example=1),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/InvoiceResponse")
     *              ),
     *              @OA\Property(property="next_page_url", type="integer", example=1),
     *              @OA\Property(property="path", type="integer", example=1),
     *              @OA\Property(property="per_page", type="integer", example=1),
     *              @OA\Property(property="prev_page_url", type="integer", example=1),
     *              @OA\Property(property="to", type="integer", example=1),
     *              @OA\Property(property="total", type="integer", example=1),
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Returns when user is not authenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Returns when the resource is not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Resource not found"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Returns when the method is not allowed for the requested route",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route"),
     *          )
     *      ),
     *     security={{ "apiAuth": {} }}
     * )
     */
    public function index()
    {
        if (app('auth')->parseToken()->getPayload()->get('role') == "admin") {
            return $this->preferredFormat(Invoice::with('invoicelines', 'invoicelines.product', 'payment', 'payment.payment_details')->orderBy('invoice_date', 'DESC')->filter()->paginate());
        } else {
            return $this->preferredFormat(Invoice::with('invoicelines', 'invoicelines.product', 'payment', 'payment.payment_details')->where('user_id', Auth::user()->id)->orderBy('invoice_date', 'DESC')->filter()->paginate());
        }
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
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Returns when user is not authenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Returns when requested item is not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Requested item not found"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Returns when the method is not allowed for the requested route",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Returns when the server was not able to process the content",
     *      ),
     *     security={{ "apiAuth": {} }}
     * )
     */
    public function store(StoreInvoice $request)
    {
        $input = $request->except(['cart_id']);
        $input['user_id'] = Auth::user()->id;
        $input['invoice_date'] = now();
        $input['invoice_number'] = IdGenerator::generate(['table' => 'invoices', 'field' => 'invoice_number', 'length' => 14, 'prefix' => 'INV-' . now()->year]);

        $invoice = Invoice::create($input);

        $subTotalPrice = 0;

        $cart = Cart::with('cartItems', 'cartItems.product')->findOrFail($request->input('cart_id'));
        // Iterate through cart items to calculate discounted prices
        foreach ($cart->cartItems as $cartItem) {
            $quantity = $cartItem['quantity'];
            $unitPrice = $cartItem['product']->price;

            $discountedPrice = null;
            if ($cartItem->discount_percentage !== null) {
                $discountedPrice = $cartItem->product->price * (1 - ($cartItem->discount_percentage / 100));
                // Round the discounted price to two decimal places
                $discountedPrice = round($discountedPrice, 2);
            }

            // Decrement the product stock
            UpdateProductInventory::dispatch($cartItem['product']->id, $quantity);

            // Create the invoice line
            $invoice->invoicelines()->create([
                'product_id' => $cartItem['product']->id,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'discount_percentage' => $cartItem->discount_percentage,
                'discounted_price' => $discountedPrice
            ]);

            $subTotalPrice += $cartItem->discount_percentage ? $quantity * ($cartItem->product->price * (1 - ($cartItem->discount_percentage / 100))) : $quantity * $unitPrice;
        }

        $discountAmount = $subTotalPrice * ($cart->additional_discount_percentage / 100);
        $totalPrice = ($cart->additional_discount_percentage) ? $subTotalPrice - $discountAmount : $subTotalPrice;
        $invoice->update(['subtotal' => $subTotalPrice,
            'total' => $totalPrice,
            'additional_discount_percentage' => $cart->additional_discount_percentage,
            'additional_discount_amount' => $discountAmount]);

        // After creating the invoice
        $paymentMethod = $request->input('payment_method');

        // Create a payment record
        $payment = new Payment([
            'invoice_id' => $invoice->id,
            'payment_method' => $paymentMethod
        ]);

        // Check payment method and create corresponding payment details
        if ($paymentMethod === 'Bank Transfer') {
            $request->validate([
                'payment_details.bank_name' => 'required|string|max:255|regex:/^[a-zA-Z ]+$/',
                'payment_details.account_name' => 'required|string|max:255|regex:/^[a-zA-Z0-9 .\'-]+$/',
                'payment_details.account_number' => 'required|string|max:255|regex:/^\d+$/',
            ]);
            $bankTransferDetailsData = $request->input('payment_details');
            $bankTransferDetails = new PaymentBankTransferDetails($bankTransferDetailsData);
            $bankTransferDetails->save();

            $payment->payment_details_id = $bankTransferDetails->id;
            $payment->payment_details_type = PaymentBankTransferDetails::class;
        }

        if ($paymentMethod === 'Cash on Delivery') {
            $cashOnDeliveryDetails = new PaymentCashOnDeliveryDetails();
            $cashOnDeliveryDetails->save();

            $payment->payment_details_id = $cashOnDeliveryDetails->id;
            $payment->payment_details_type = PaymentCashOnDeliveryDetails::class;
        }

        if ($paymentMethod === 'Credit Card') {
            $request->validate([
                'payment_details.credit_card_number' => 'required|string|regex:/^\d{4}-\d{4}-\d{4}-\d{4}$/',
                'payment_details.expiration_date' => 'required|date_format:m/Y|after:today',
                'payment_details.cvv' => 'required|string|regex:/^\d{3,4}$/',
                'payment_details.card_holder_name' => 'required|string|max:255|regex:/^[a-zA-Z ]+$/',
            ]);
            $creditCardDetailsData = $request->input('payment_details');
            $creditCardDetails = new PaymentCreditCardDetails($creditCardDetailsData);
            $creditCardDetails->save();

            $payment->payment_details_id = $creditCardDetails->id;
            $payment->payment_details_type = PaymentCreditCardDetails::class;
        }

        if ($paymentMethod === 'Buy Now Pay Later') {
            $request->validate([
                'payment_details.monthly_installments' => 'required|numeric',
            ]);
            $bnplDetailsData = $request->input('payment_details');
            $bnplDetails = new PaymentBnplDetails($bnplDetailsData);
            $bnplDetails->save();

            $payment->payment_details_id = $bnplDetails->id;
            $payment->payment_details_type = PaymentBnplDetails::class;
        }

        if ($paymentMethod === 'Gift Card') {
            $request->validate([
                'payment_details.gift_card_number' => 'required|string|max:255|regex:/^[a-zA-Z0-9]+$/',
                'payment_details.validation_code' => 'required|string|max:255|regex:/^[a-zA-Z0-9]+$/',
            ]);
            $giftCardDetailsData = $request->input('payment_details');
            $giftCardDetails = new PaymentGiftCardDetails($giftCardDetailsData);
            $giftCardDetails->save();

            $payment->payment_details_id = $giftCardDetails->id;
            $payment->payment_details_type = PaymentGiftCardDetails::class;
        }

        // Save the payment record
        $payment->save();

        if (App::environment('local')) {
            SendCheckoutEmail::dispatch($invoice->id, Auth::user());
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
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/InvoiceResponse")
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Returns when user is not authenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Returns when the requested item is not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Requested item not found"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Returns when the method is not allowed for the requested route",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route"),
     *          )
     *      ),
     *     security={{ "apiAuth": {} }}
     * )
     */
    public function show($id)
    {
        if (app('auth')->parseToken()->getPayload()->get('role') == "admin") {
            return $this->preferredFormat(Invoice::with('invoicelines', 'invoicelines.product', 'payment', 'payment.payment_details')->where('id', $id)->first());
        } else {
            return $this->preferredFormat(Invoice::with('invoicelines', 'invoicelines.product', 'payment', 'payment.payment_details')->where('id', $id)->where('user_id', Auth::user()->id)->first());
        }
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
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Returns when user is not authenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Returns when the requested item is not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Requested item not found"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Returns when the method is not allowed for the requested route",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route"),
     *          )
     *      ),
     *     security={{ "apiAuth": {} }}
     * )
     */
    public function downloadPDF($invoice_number)
    {
        if (Storage::exists('invoices/' . $invoice_number . '.pdf')) {
            return Storage::download('invoices/' . $invoice_number . '.pdf', $invoice_number . '.pdf');
        } else {
            return $this->preferredFormat(['message' => 'Document not created. Try again later.'], ResponseAlias::HTTP_NOT_FOUND);
        }
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
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Returns when user is not authenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Returns when the requested item is not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Requested item not found"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Returns when the method is not allowed for the requested route",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route"),
     *          )
     *      ),
     *     security={{ "apiAuth": {} }}
     * )
     */
    public function downloadPDFStatus($invoice_number)
    {
        $status = Download::where('name', $invoice_number)->first(['status']);
        if (empty($status)) {
            return $this->preferredFormat(['status' => 'NOT_INITIATED'], ResponseAlias::HTTP_BAD_REQUEST);
        } else {
            return $this->preferredFormat($status, ResponseAlias::HTTP_OK);
        }
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
     *          required=true,
     *          description="Invoice request object",
     *          @OA\JsonContent(ref="#/components/schemas/InvoiceRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Result of the update",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="success",
     *                       type="boolean",
     *                       example=true,
     *                       description=""
     *                  ),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Returns when user is not authenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Returns when the resource is not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Resource not found"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Returns when the method is not allowed for the requested route",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Returns when the server was not able to process the content",
     *      ),
     *     security={{ "apiAuth": {} }}
     * )
     */
    public function updateStatus($id, Request $request)
    {
        $request->validate([
            'status' => Rule::in("AWAITING_FULFILLMENT", "ON_HOLD", "AWAITING_SHIPMENT", "SHIPPED", "COMPLETED"),
            'status_message' => ['string', 'between:5,50', 'nullable', new SubscriptSuperscriptRule()]
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
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="current_page", type="integer", example=1),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/InvoiceResponse")
     *              ),
     *              @OA\Property(property="next_page_url", type="integer", example=1),
     *              @OA\Property(property="path", type="integer", example=1),
     *              @OA\Property(property="per_page", type="integer", example=1),
     *              @OA\Property(property="prev_page_url", type="integer", example=1),
     *              @OA\Property(property="to", type="integer", example=1),
     *              @OA\Property(property="total", type="integer", example=1),
     *          )
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Returns when the requested item is not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Requested item not found"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Returns when the method is not allowed for the requested route",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route"),
     *          )
     *      ),
     * )
     */
    public function search(Request $request)
    {
        $q = $request->get('q');

        if (app('auth')->parseToken()->getPayload()->get('role') == "admin") {
            return $this->preferredFormat(Invoice::with('invoicelines', 'invoicelines.product', 'payment', 'payment.payment_details')->where('invoice_number', 'like', "%$q%")->orWhere('billing_address', 'like', "%$q%")->orWhere('status', 'like', "%$q%")->orderBy('invoice_date', 'DESC')->paginate());
        } else {
            return $this->preferredFormat(Invoice::with('invoicelines', 'invoicelines.product', 'payment', 'payment.payment_details')->where('user_id', Auth::user()->id)->orWhere('invoice_number', 'like', "%$q%")->orWhere('billing_address', 'like', "%$q%")->orWhere('status', 'like', "%$q%")->orderBy('invoice_date', 'DESC')->paginate());
        }
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
     *      @OA\Response(
     *          response=200,
     *          description="Result of the update",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="success",
     *                       type="boolean",
     *                       example=true,
     *                       description=""
     *                  ),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Returns when user is not authenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Returns when the resource is not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Resource not found"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Returns when the method is not allowed for the requested route",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Returns when the server was not able to process the content",
     *      ),
     *     security={{ "apiAuth": {} }}
     * )
     */
    public function update(StoreInvoice $request, $id)
    {
        return $this->preferredFormat(['success' => (bool)Invoice::where('id', $id)->where('customer_id', Auth::user()->id)->update($request->all())], ResponseAlias::HTTP_OK);
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
     *      @OA\Response(
     *          response=204,
     *          description="Successful operation"
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Returns when user is not authenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Returns when the resource is not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Resource not found"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=409,
     *          description="Returns when the entity is used elsewhere",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Returns when the method is not allowed for the requested route",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Returns when the server was not able to process the content",
     *      ),
     *     security={{ "apiAuth": {} }}
     * ),
     */
    public function destroy(DestroyInvoice $request, $id)
    {
        try {
            Invoice::find($id)->where('customer_id', Auth::user()->id)->delete();
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
