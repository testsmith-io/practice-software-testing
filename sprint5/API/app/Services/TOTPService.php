<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use PragmaRX\Google2FA\Google2FA;
use Carbon\Carbon;

class TOTPService
{
    private $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function setupTOTP($user)
    {
        $restrictedEmails = [
            'customer@practicesoftwaretesting.com',
            'admin@practicesoftwaretesting.com'
        ];

        if (in_array($user->email, $restrictedEmails)) {
            return ['error' => 'TOTP cannot be set up for this account', 'status' => 403];
        }

        if ($user->totp_enabled) {
            return ['error' => 'TOTP already enabled', 'status' => 400];
        }

        $secret = $this->google2fa->generateSecretKey();
        $user->totp_secret = $secret;
        $user->save();
        Cache::forget('auth.user.' . $user->id);

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            'Practice Software Testing',
            $user->email,
            $secret
        );

        return ['secret' => $secret, 'qrCodeUrl' => $qrCodeUrl, 'status' => 200];
    }

    public function verifyTOTP($user, $totpCode)
    {
        if (!$this->google2fa->verifyKey($user->totp_secret, $totpCode)) {
            return ['error' => 'Invalid TOTP', 'status' => 400];
        }

        $user->totp_enabled = true;
        $user->totp_verified_at = Carbon::now();
        $user->save();
        Cache::forget('auth.user.' . $user->id);

        return ['message' => 'TOTP enabled successfully', 'status' => 200];
    }
}
