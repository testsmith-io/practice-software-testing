<?php

namespace App\Http\Controllers;

use App\Models\PaymentBankTransferDetails;
use App\Models\PaymentBnplDetails;
use App\Models\PaymentCashOnDeliveryDetails;
use App\Models\PaymentCreditCardDetails;
use App\Models\PaymentGiftCardDetails;
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
     *           description="Invoice request object",
     *           @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"payment_method", "payment_details"},
     *                  @OA\Property(
     *                      property="payment_method",
     *                      type="string",
     *                      example="Credit Card"
     *                  ),
     *                  @OA\Property(
     *                      property="payment_details",
     *                      type="object",
     *                      oneOf={
     *                          @OA\Schema(ref="#/components/schemas/BankTransferDetails"),
     *                          @OA\Schema(ref="#/components/schemas/CreditCardDetails"),
     *                          @OA\Schema(ref="#/components/schemas/BuyNowPayLaterDetails"),
     *                          @OA\Schema(ref="#/components/schemas/GiftCardDetails"),
     *                          @OA\Schema(type="object", title="CashOnDeliveryDetails")
     *                      }
     *                  )
     *              )
     *           )
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example="Success status"),
     *          )
     *      )
     * )
     */
    public function check(Request $request)
    {
        $paymentMethod = $request->input('payment_method');
        if ($paymentMethod === 'Bank Transfer') {
            $request->validate([
                'payment_details.bank_name' => 'required|string|max:255|regex:/^[a-zA-Z ]+$/',
                'payment_details.account_name' => 'required|string|max:255|regex:/^[a-zA-Z0-9 .\'-]+$/',
                'payment_details.account_number' => 'required|string|max:255|regex:/^\d+$/',
            ]);
        }

        if ($paymentMethod === 'Cash on Delivery') {

        }

        if ($paymentMethod === 'Credit Card') {
            $request->validate([
                'payment_details.credit_card_number' => 'required|string|regex:/^\d{4}-\d{4}-\d{4}-\d{4}$/',
                'payment_details.expiration_date' => 'required|date_format:m/Y|after:today',
                'payment_details.cvv' => 'required|string|regex:/^\d{3,4}$/',
                'payment_details.card_holder_name' => 'required|string|max:255|regex:/^[a-zA-Z ]+$/',
            ]);
        }

        if ($paymentMethod === 'Buy Now Pay Later') {
            $request->validate([
                'payment_details.monthly_installments' => 'required|numeric',
            ]);
        }
        if ($paymentMethod === 'Gift Card') {
            $request->validate([
                'payment_details.gift_card_number' => 'required|string|max:255|regex:/^[a-zA-Z0-9]+$/',
                'payment_details.validation_code' => 'required|string|max:255|regex:/^[a-zA-Z0-9]+$/',
            ]);
        }
        return $this->preferredFormat(['message' => 'Payment was successful'], ResponseAlias::HTTP_OK);
    }

}
