<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Http\Request;

class TOTPController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:users');
        $this->middleware('assign.guard:users');
    }

    /**
     * @OA\Post(
     *     path="/totp/setup",
     *     summary="Setup TOTP for the authenticated user",
     *     description="Generates a TOTP secret and QR code URL for the user to scan and enables TOTP setup.",
     *     tags={"TOTP"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="TOTP setup successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="secret", type="string", description="The TOTP secret key"),
     *             @OA\Property(property="qrCodeUrl", type="string", description="URL for the QR code")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="TOTP already enabled or another error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", description="Error message")
     *         )
     *     )
     * )
     */
    public function setup(Request $request)
    {
        $user = $request->user();

        $restrictedEmails = [
            'customer@practicesoftwaretesting.com',
            'admin@practicesoftwaretesting.com'
        ];

        if (in_array($user->email, $restrictedEmails)) {
            return response()->json(['error' => 'TOTP cannot be set up for this account'], 403);
        }

        if ($user->totp_enabled) {
            return response()->json(['error' => 'TOTP already enabled'], 400);
        }

        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $user->totp_secret = $secret;
        $user->save();
        Cache::forget('auth.user.' . $user->id);

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            'Practice Software Testing',
            $user->email,
            $secret
        );

        return response()->json(['secret' => $secret, 'qrCodeUrl' => $qrCodeUrl]);
    }

    /**
     * @OA\Post(
     *     path="/totp/verify",
     *     summary="Verify TOTP code for the authenticated user",
     *     description="Validates the submitted TOTP code and enables TOTP if verification is successful.",
     *     tags={"TOTP"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", description="The user's access token"),
     *             @OA\Property(property="totp", type="string", description="The 6-digit TOTP code", example="123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="TOTP verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="TOTP enabled successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid TOTP code or another error",
     *         @OA\JsonContent(
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
        $google2fa = new Google2FA();

        if (!$google2fa->verifyKey($user->totp_secret, $validated['totp'])) {
            return response()->json(['error' => 'Invalid TOTP'], 400);
        }

        $user->totp_enabled = true;
        $user->totp_verified_at = now();
        $user->save();
        Cache::forget('auth.user.' . $user->id);

        return response()->json(['message' => 'TOTP enabled successfully']);
    }

}
