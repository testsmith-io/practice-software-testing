<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class PaymentController extends Controller
{
    /**
     * @OA\Post(
     *      path="/payment/check",
     *      operationId="checkPayment",
     *      tags={"Payment"},
     *      summary="Check payment",
     *      description="Check payment",
     *      @OA\RequestBody(
     *           required=true,
     *           description="Payment request object",
     *           @OA\JsonContent(ref="#/components/schemas/PaymentRequest")
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              title="PaymentResponse",
     *              @OA\Property(property="message", type="string", example="Success status"),
     *          )
     *      )
     * )
     */
    public function check(Request $request)
    {
        $paymentMethod = $request->input('payment_method');
        if ($paymentMethod === 'bank-transfer') {
            $request->validate([
                'payment_details.bank_name' => 'required|string|max:255|regex:/^[a-zA-Z ]+$/',
                'payment_details.account_name' => 'required|string|max:255|regex:/^[a-zA-Z0-9 .\'-]+$/',
                'payment_details.account_number' => 'required|string|max:255|regex:/^\d+$/',
            ]);
        }

        if ($paymentMethod === 'cash-on-delivery') {

        }

        if ($paymentMethod === 'credit-card') {
            $request->validate([
                'payment_details.credit_card_number' => 'required|string|regex:/^\d{4}-\d{4}-\d{4}-\d{4}$/',
                'payment_details.expiration_date' => 'required|date_format:m/Y|after:today',
                'payment_details.cvv' => 'required|string|regex:/^\d{3,4}$/',
                'payment_details.card_holder_name' => 'required|string|max:255|regex:/^[a-zA-Z ]+$/',
            ]);
        }

        if ($paymentMethod === 'buy-now-pay-later') {
            $request->validate([
                'payment_details.monthly_installments' => 'required|numeric',
            ]);
        }
        if ($paymentMethod === 'gift-card') {
            $request->validate([
                'payment_details.gift_card_number' => 'required|string|max:255|regex:/^[a-zA-Z0-9]+$/',
                'payment_details.validation_code' => 'required|string|max:255|regex:/^[a-zA-Z0-9]+$/',
            ]);
        }
        return $this->preferredFormat(['message' => 'Payment was successful'], ResponseAlias::HTTP_OK);
    }

}
