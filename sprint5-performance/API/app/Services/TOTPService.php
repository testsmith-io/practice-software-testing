<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PragmaRX\Google2FA\Google2FA;

class TOTPService
{
    private $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function setupTOTP($user)
    {
        Log::info('TOTP setup initiated', ['user_id' => $user->id, 'email' => $user->email]);

        $restrictedEmails = [
            'customer@practicesoftwaretesting.com',
            'admin@practicesoftwaretesting.com'
        ];

        if (in_array($user->email, $restrictedEmails)) {
            Log::warning('TOTP setup blocked for restricted email', ['user_id' => $user->id]);
            return ['error' => 'TOTP cannot be set up for this account', 'status' => 403];
        }

        if ($user->totp_enabled) {
            Log::info('TOTP already enabled for user', ['user_id' => $user->id]);
            return ['error' => 'TOTP already enabled', 'status' => 400];
        }

        $secret = $this->google2fa->generateSecretKey();
        $user->totp_secret = $secret;
        $user->save();
        Cache::forget('auth.user.' . $user->id);

        Log::info('TOTP secret generated and saved', ['user_id' => $user->id]);

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            'Practice Software Testing',
            $user->email,
            $secret
        );

        Log::info('TOTP QR code URL generated', ['user_id' => $user->id]);

        return ['secret' => $secret, 'qrCodeUrl' => $qrCodeUrl, 'status' => 200];
    }

    public function verifyTOTP($user, $totpCode)
    {
        Log::info('TOTP verification attempt', ['user_id' => $user->id]);

        if (!$this->google2fa->verifyKey($user->totp_secret, $totpCode)) {
            Log::warning('TOTP verification failed', ['user_id' => $user->id]);
            return ['error' => 'Invalid TOTP', 'status' => 400];
        }

        $user->totp_enabled = true;
        $user->totp_verified_at = Carbon::now();
        $user->save();
        Cache::forget('auth.user.' . $user->id);

        Log::info('TOTP successfully verified and enabled', ['user_id' => $user->id]);

        return ['message' => 'TOTP enabled successfully', 'status' => 200];
    }
}
