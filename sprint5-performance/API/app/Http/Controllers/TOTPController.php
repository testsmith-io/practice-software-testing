<?php

namespace App\Http\Controllers;

use App\Services\TOTPService;
use Illuminate\Http\Request;

class TOTPController extends Controller
{
    private $totpService;

    public function __construct(TOTPService $totpService)
    {
        $this->totpService = $totpService;
        $this->middleware('auth:users');
        $this->middleware('assign.guard:users');
    }

    /**
     * @OA\Post(
     *     path="/totp/setup",
     *     summary="Setup TOTP for the authenticated user",
     *     description="Generates a TOTP secret and QR code URL for the user to scan and enables TOTP setup.",
     *     tags={"TOTP"},
     *     security={{ "apiAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="TOTP setup successful",
     *         @OA\JsonContent(
     *             title="TOTPSetupResponse",
     *             @OA\Property(property="secret", type="string", description="The TOTP secret key"),
     *             @OA\Property(property="qrCodeUrl", type="string", description="URL for the QR code")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="TOTP already enabled or another error",
     *         @OA\JsonContent(
     *             title="TOTPErrorResponse",
     *             @OA\Property(property="error", type="string", description="Error message")
     *         )
     *     )
     * )
     */
    public function setup(Request $request)
    {
        $user = $request->user();
        $response = $this->totpService->setupTOTP($user);

        return response()->json(
            array_diff_key($response, ['status' => null]),
            $response['status']
        );
    }

    /**
     * @OA\Post(
     *     path="/totp/verify",
     *     summary="Verify TOTP code for the authenticated user",
     *     description="Validates the submitted TOTP code and enables TOTP if verification is successful.",
     *     tags={"TOTP"},
     *     security={{ "apiAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             title="TOTPVerifyRequest",
     *             @OA\Property(property="access_token", type="string", description="The user's access token"),
     *             @OA\Property(property="totp", type="string", description="The 6-digit TOTP code", example="123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="TOTP verified successfully",
     *         @OA\JsonContent(
     *             title="TOTPVerifyResponse",
     *             @OA\Property(property="message", type="string", description="Success message", example="TOTP enabled successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid TOTP code or another error",
     *         @OA\JsonContent(
     *             title="TOTPErrorResponse",
     *             @OA\Property(property="error", type="string", description="Error message", example="Invalid TOTP")
     *         )
     *     )
     * )
     */
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'totp' => 'required|digits:6',
        ]);

        $user = $request->user();
        $response = $this->totpService->verifyTOTP($user, $validated['totp']);

        return response()->json(
            array_diff_key($response, ['status' => null]),
            $response['status']
        );
    }

}
