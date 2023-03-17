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
        return $this->preferredFormat(['message' => 'Payment was successful'], ResponseAlias::HTTP_OK);
    }

}
