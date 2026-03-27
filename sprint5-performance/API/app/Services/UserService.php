<?php

namespace App\Services;

use App\Mail\ForgetPassword;
use App\Mail\Register;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PragmaRX\Google2FA\Google2FA;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserService
{
    const MAX_LOGIN_ATTEMPTS = 3;

    public function getAllUsers()
    {
        Log::debug("Fetching users");
        return User::paginate();
    }

    public function registerUser($data)
    {
        Log::info("Registering user with email: {$data['email']}");
        $data['role'] = 'user';
        $data['password'] = app('hash')->make($data['password']);

        $data = $this->extractAddressFields($data);

        if (App::environment('local')) {
            Log::debug("Sending registration email to: {$data['email']}");
            Mail::to([$data['email']])->send(new Register("{$data['first_name']} {$data['last_name']}", $data['email'], $data['password']));
        }

        $user = User::create($data);
        Log::info("User registered: ID {$user->id}");

        return $user;
    }

    public function login($credentials)
    {
        Log::info("Login attempt with email: " . ($credentials['email'] ?? '[access_token]'));

        if (isset($credentials['email']) && isset($credentials['password'])) {
            $user = User::where('email', $credentials['email'])->first();

            if ($user && $user->role != "admin" && $user->failed_login_attempts >= self::MAX_LOGIN_ATTEMPTS) {
                Log::warning("Account locked for user: {$user->email}");
                return ['error' => 'Account locked, too many failed attempts. Please contact the administrator.'];
            }

            $token = app('auth')->attempt($credentials);

            if (!$token) {
                Log::warning("Invalid credentials for email: {$credentials['email']}");
                if ($user && $user->role != "admin") {
                    $this->incrementLoginAttempts($user);
                }
                return ['error' => 'Unauthorized'];
            }

            if (!$user->enabled) {
                Log::info("Disabled account attempted login: {$user->email}");
                return ['error' => 'Account disabled'];
            }

            if ($user->totp_enabled) {
                Log::info("TOTP login required for: {$user->email}");
                app('auth')->invalidate($token);
                $tempToken = app('auth')->claims(['restricted' => true])->attempt($credentials);

                return [
                    'message' => 'TOTP required',
                    'requires_totp' => true,
                    'access_token' => $tempToken,
                ];
            }

            $this->resetLoginAttempts($user);
            Log::info("User logged in: {$user->email}");
            return ['token' => $token];
        }

        if (isset($credentials['access_token']) && isset($credentials['totp'])) {
            try {
                $payload = app('auth')->setToken($credentials['access_token'])->getPayload();
                $user = JWTAuth::setToken($credentials['access_token'])->toUser();

                Log::info("Handling TOTP login for user ID: {$user->id}");

                if (!$payload->get('restricted', false)) {
                    return ['error' => 'Unauthorized token usage'];
                }

                if (!$user || !$user->totp_enabled) {
                    return ['error' => 'Unauthorized'];
                }

                $google2fa = new Google2FA();
                if (!$google2fa->verifyKey($user->totp_secret, $credentials['totp'])) {
                    Log::warning("Invalid TOTP for user: {$user->email}");
                    return ['error' => 'Invalid TOTP'];
                }

                $finalToken = app('auth')->claims(['restricted' => false])->login($user);
                Log::info("TOTP login successful for user: {$user->email}");

                return ['token' => $finalToken];
            } catch (Exception $e) {
                Log::error("TOTP login failed: " . $e->getMessage());
                return ['error' => 'Invalid or expired token'];
            }
        }

        Log::warning("Invalid login request format");
        return ['error' => 'Invalid login request'];
    }

    public function handleTotpLogin($accessToken, $totpCode)
    {
        Log::info("Handling TOTP login");

        $payload = JWTAuth::setToken($accessToken)->getPayload();

        if (!$payload->get('restricted', false)) {
            return ['error' => 'Unauthorized token usage'];
        }

        $user = User::find(JWTAuth::setToken($accessToken)->toUser()->id);
        if (!$user || !$user->totp_enabled) {
            return ['error' => 'Unauthorized'];
        }

        $google2fa = new Google2FA();
        if (!$google2fa->verifyKey($user->totp_secret, $totpCode)) {
            return ['error' => 'Invalid TOTP'];
        }

        $finalToken = app('auth')->claims(['restricted' => false])->login($user);
        Log::info("TOTP login successful for user ID: {$user->id}");

        return ['token' => $finalToken];
    }

    public function resetPassword($email)
    {
        Log::info("Resetting password for: {$email}");
        $user = User::where('email', $email)->firstOrFail();
        $newPassword = 'welcome02'; // NOSONAR
        $user->update(['password' => app('hash')->make($newPassword)]);

        if (App::environment('local')) {
            Log::debug("Sending reset email to: {$email}");
            Mail::to($email)->send(new ForgetPassword("{$user->first_name} {$user->last_name}", $newPassword));
        }

        return ['success' => true];
    }

    public function changePassword($user, $currentPassword, $newPassword)
    {
        Log::info("Changing password for user ID: {$user->id}");

        if (!Hash::check($currentPassword, $user->password)) {
            Log::warning("Current password mismatch for user ID: {$user->id}");
            return ['error' => 'Current password does not match'];
        }

        if ($currentPassword === $newPassword) {
            return ['error' => 'New password cannot be the same as current password'];
        }

        $user->password = app('hash')->make($newPassword);
        $user->save();
        Log::info("Password updated for user ID: {$user->id}");

        return ['success' => true];
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        Log::info("Deleting user ID: {$user->id}");

        try {
            $user->delete();
        } catch (QueryException $e) {
            Log::error("Failed to delete user ID: {$user->id} - " . $e->getMessage());
            throw $e;
        }
    }

    public function updateUser($id, $data, $currentUserId, $currentUserRole)
    {
        Log::info("Updating user ID: {$id}");

        $user = User::findOrFail($id);

        if ($currentUserId !== $id && $currentUserRole !== 'admin') {
            Log::warning("Unauthorized update attempt by user ID: {$currentUserId}");
            throw new Exception('You can only update your own data.');
        }

        if (isset($data['role']) && $currentUserRole !== 'admin') {
            Log::warning("Role update blocked for non-admin user ID: {$currentUserId}");
            throw new Exception('Only admins can update the role.');
        }

        if ($currentUserRole !== 'admin') {
            unset($data['role']);
        }

        unset($data['password']);
        $data = $this->extractAddressFields($data);

        $success = $user->update($data);
        Cache::forget('auth.user.' . $user->id);

        Log::info("User ID: {$id} update status: " . ($success ? 'success' : 'fail'));
        return ['success' => (bool)$success];
    }

    public function searchUsers($query)
    {
        Log::debug("Searching users with query: {$query}");

        return User::where('role', '=', 'user')
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%$query%")
                    ->orWhere('last_name', 'like', "%$query%")
                    ->orWhere('email', 'like', "%$query%")
                    ->orWhere('city', 'like', "%$query%");
            })->paginate();
    }

    private function incrementLoginAttempts($user)
    {
        Log::debug("Incrementing login attempts for user ID: {$user->id}");
        if ($user->failed_login_attempts < self::MAX_LOGIN_ATTEMPTS) {
            $user->increment('failed_login_attempts');
        }
    }

    private function resetLoginAttempts($user)
    {
        Log::debug("Resetting login attempts for user ID: {$user->id}");
        $user->update(['failed_login_attempts' => 0]);
    }

    public function getAuthenticatedUser()
    {
        $user = Auth::user();
        Log::debug("Fetching authenticated user ID: {$user->id}");
        return $user;
    }

    public function logout()
    {
        try {
            $user = Auth::user();
            JWTAuth::invalidate(JWTAuth::getToken());
            Log::info("User logged out: {$user->email}");
            return ['message' => 'Successfully logged out'];
        } catch (JWTException $e) {
            Log::error("Logout failed: " . $e->getMessage());
            return ['error' => 'Failed to logout, please try again.'];
        }
    }

    public function refreshToken()
    {
        Log::debug("Refreshing JWT token");
        return app('auth')->refresh(true, false);
    }

    public function getUserById($id, $currentUserId, $currentUserRole)
    {
        Log::debug("Fetching user ID: {$id}");

        if ($currentUserRole === "admin" || $currentUserId == $id) {
            return User::findOrFail($id);
        }

        Log::warning("Unauthorized access to user ID: {$id} by user ID: {$currentUserId}");
        throw new Exception('You are not authorized to view this user.');
    }

    public function patchUser($id, $data, $currentUserId, $currentUserRole)
    {
        Log::info("Patching user ID: {$id}");

        $user = User::findOrFail($id);

        if ($currentUserId === $id || $currentUserRole === "admin") {
            if (isset($data['role']) && $currentUserRole !== "admin") {
                throw new Exception('Only admins can update the role.');
            }

            if ($currentUserRole !== "admin") {
                unset($data['role']);
            }

            $data = $this->extractAddressFields($data);
            $user->update($data);
            Cache::forget('auth.user.' . $user->id);

            Log::info("Patch update successful for user ID: {$id}");
            return ['success' => true];
        }

        Log::warning("Unauthorized patch attempt on user ID: {$id}");
        throw new Exception('You can only update your own data.');
    }

    public function extractAddressFields($data)
    {
        if (isset($data['address']) && is_array($data['address'])) {
            $data['street'] = $data['address']['street'] ?? null;
            $data['city'] = $data['address']['city'] ?? null;
            $data['state'] = $data['address']['state'] ?? null;
            $data['country'] = $data['address']['country'] ?? null;
            $data['postal_code'] = $data['address']['postal_code'] ?? null;
            unset($data['address']);
        }
        return $data;
    }
}
