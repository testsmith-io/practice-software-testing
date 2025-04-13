<?php

namespace App\Services;

use App\Mail\ForgetPassword;
use App\Mail\Register;
use App\Models\User;
use Exception;
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

    public function getAllUsers($role = 'user')
    {
        Log::debug("Fetching all users with role: {$role}");
        return User::where('role', '=', $role)->paginate();
    }

    public function registerUser($data)
    {
        Log::info("Registering user: {$data['email']}");

        $data['role'] = 'user';
        $data['password'] = app('hash')->make($data['password']);
        $data = $this->extractAddressFields($data);

        if (App::environment('local')) {
            Mail::to([$data['email']])->send(new Register("{$data['first_name']} {$data['last_name']}", $data['email'], $data['password']));
            Log::debug("Registration mail sent to: {$data['email']}");
        }

        $user = User::create($data);
        Log::info("User created successfully: ID {$user->id}");

        return $user;
    }

    public function login($credentials)
    {
        Log::info("Login attempt with credentials", ['credentials' => $credentials]);

        if (isset($credentials['email']) && isset($credentials['password'])) {
            $user = User::where('email', $credentials['email'])->first();

            if ($user && $user->role !== "admin" && $user->failed_login_attempts >= self::MAX_LOGIN_ATTEMPTS) {
                Log::warning("Account locked due to too many failed attempts for: {$user->email}");
                return ['error' => 'Account locked, too many failed attempts. Please contact the administrator.'];
            }

            $token = app('auth')->attempt($credentials);

            if (!$token) {
                Log::warning("Login failed for: {$credentials['email']}");
                if ($user && $user->role !== "admin") {
                    $this->incrementLoginAttempts($user);
                }
                return ['error' => 'Unauthorized'];
            }

            if (!$user->enabled) {
                Log::warning("Login blocked: Account disabled for {$user->email}");
                return ['error' => 'Account disabled'];
            }

            if ($user->totp_enabled) {
                Log::info("TOTP required for: {$user->email}");
                app('auth')->invalidate($token);
                $tempToken = app('auth')->claims(['restricted' => true])->attempt($credentials);

                return [
                    'message' => 'TOTP required',
                    'requires_totp' => true,
                    'access_token' => $tempToken,
                ];
            }

            $this->resetLoginAttempts($user);
            Log::info("Login successful for: {$user->email}");
            return ['token' => $token];
        }

        if (isset($credentials['access_token']) && isset($credentials['totp'])) {
            Log::debug("TOTP login attempt");

            try {
                $payload = app('auth')->setToken($credentials['access_token'])->getPayload();
                if (!$payload->get('restricted', false)) {
                    Log::warning("Unauthorized token usage attempt");
                    return ['error' => 'Unauthorized token usage'];
                }

                $user = User::find(JWTAuth::setToken($credentials['access_token'])->toUser()->id);

                if (!$user || !$user->totp_enabled) {
                    Log::warning("Unauthorized TOTP login");
                    return ['error' => 'Unauthorized'];
                }

                $google2fa = new Google2FA();
                if (!$google2fa->verifyKey($user->totp_secret, $credentials['totp'])) {
                    Log::warning("Invalid TOTP for: {$user->email}");
                    return ['error' => 'Invalid TOTP'];
                }

                $finalToken = app('auth')->claims(['restricted' => false])->login($user);
                Log::info("TOTP login successful for: {$user->email}");
                return ['token' => $finalToken];
            } catch (Exception $e) {
                Log::error("TOTP login error: " . $e->getMessage());
                return ['error' => 'Invalid or expired token'];
            }
        }

        Log::warning("Invalid login request format");
        return ['error' => 'Invalid login request'];
    }

    public function handleTotpLogin($accessToken, $totpCode)
    {
        Log::debug("Handling TOTP login");

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
        return ['token' => $finalToken];
    }

    public function resetPassword($email)
    {
        Log::info("Resetting password for: {$email}");

        $user = User::where('email', $email)->firstOrFail();
        $newPassword = 'welcome02';
        $user->update(['password' => app('hash')->make($newPassword)]);

        if (App::environment('local')) {
            Mail::to($email)->send(new ForgetPassword("{$user->first_name} {$user->last_name}", $newPassword));
            Log::debug("Password reset mail sent to: {$email}");
        }

        return ['success' => true];
    }

    public function changePassword($user, $currentPassword, $newPassword)
    {
        Log::info("Password change requested for: {$user->email}");

        if (!Hash::check($currentPassword, $user->password)) {
            Log::warning("Password change failed: incorrect current password for {$user->email}");
            return ['error' => 'Current password does not match'];
        }

        if (strcmp($currentPassword, $newPassword) === 0) {
            Log::warning("Password change failed: new password same as current for {$user->email}");
            return ['error' => 'New password cannot be the same as current password'];
        }

        $user->password = app('hash')->make($newPassword);
        $user->save();

        Log::info("Password changed successfully for: {$user->email}");
        return ['success' => true];
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        Log::info("Attempting to delete user: {$user->email}");

        try {
            $user->delete();
            Log::info("User deleted successfully: {$user->email}");
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("Failed to delete user: {$user->email}, error: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateUser($id, $data, $currentUserId, $currentUserRole)
    {
        Log::debug("Updating user {$id} by {$currentUserId}");

        $user = User::findOrFail($id);

        if ($currentUserId !== $id && $currentUserRole !== 'admin') {
            Log::warning("Unauthorized update attempt on user {$id} by user {$currentUserId}");
            throw new Exception('You can only update your own data.');
        }

        if (isset($data['role']) && $currentUserRole !== 'admin') {
            Log::warning("Unauthorized role change attempt by {$currentUserId}");
            throw new Exception('Only admins can update the role.');
        }

        if ($currentUserRole !== 'admin') {
            unset($data['role']);
        }

        unset($data['password']);
        $data = $this->extractAddressFields($data);

        $success = $user->update($data);
        Cache::forget('auth.user.' . $user->id);

        Log::info("User {$id} update success: " . json_encode($success));
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

    public function getAuthenticatedUser()
    {
        $user = Auth::user();
        Log::debug("Authenticated user fetched: " . ($user ? $user->email : 'null'));
        return $user;
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            Log::info("User logged out successfully");
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
        Log::debug("Fetching user {$id} by {$currentUserId} ({$currentUserRole})");

        if ($currentUserRole === "admin" || $currentUserId == $id) {
            return User::findOrFail($id);
        }

        Log::warning("Unauthorized user access attempt by {$currentUserId}");
        throw new Exception('You are not authorized to view this user.');
    }

    public function patchUser($id, $data, $currentUserId, $currentUserRole)
    {
        $user = User::findOrFail($id);
        Log::debug("Patching user {$id} by {$currentUserId}");

        if ($currentUserId === $id || $currentUserRole === "admin") {
            if (isset($data['role']) && $currentUserRole !== "admin") {
                Log::warning("Role update denied for user {$currentUserId}");
                throw new Exception('Only admins can update the role.');
            }

            if ($currentUserRole !== "admin") {
                unset($data['role']);
            }

            $data = $this->extractAddressFields($data);
            $user->update($data);
            Cache::forget('auth.user.' . $user->id);
            Log::info("User {$id} patched successfully");
            return ['success' => true];
        }

        Log::warning("Unauthorized patch attempt by {$currentUserId} on user {$id}");
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
