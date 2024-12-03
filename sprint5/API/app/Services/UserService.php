<?php

namespace App\Services;

use App\Mail\ForgetPassword;
use App\Mail\Register;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PragmaRX\Google2FA\Google2FA;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserService
{
    const MAX_LOGIN_ATTEMPTS = 3;

    public function getAllUsers($role = 'user')
    {
        return User::where('role', '=', $role)->paginate();
    }

    public function registerUser($data)
    {
        $data['role'] = 'user';
        $data['password'] = app('hash')->make($data['password']);

        if (App::environment('local')) {
            Mail::to([$data['email']])->send(new Register("{$data['first_name']} {$data['last_name']}", $data['email'], $data['password']));
        }

        return User::create($data);
    }

    public function login($credentials)
    {
        // Case 1: Login with email and password
        if (isset($credentials['email']) && isset($credentials['password'])) {
            $user = User::where('email', $credentials['email'])->first();

            if ($user && $user->role != "admin" && $user->failed_login_attempts >= self::MAX_LOGIN_ATTEMPTS) {
                return ['error' => 'Account locked, too many failed attempts. Please contact the administrator.'];
            }

            $token = app('auth')->attempt($credentials);

            if (!$token) {
                if ($user && $user->role != "admin") {
                    $this->incrementLoginAttempts($user);
                }
                return ['error' => 'Unauthorized'];
            }

            if (!$user->enabled) {
                return ['error' => 'Account disabled'];
            }

            if ($user->totp_enabled) {
                app('auth')->invalidate($token);
                $tempToken = app('auth')->claims(['restricted' => true])->attempt($credentials);

                return [
                    'message' => 'TOTP required',
                    'requires_totp' => true,
                    'access_token' => $tempToken,
                ];
            }

            $this->resetLoginAttempts($user);
            return ['token' => $token];
        }

        // Case 2: Login with access_token and TOTP
        if (isset($credentials['access_token']) && isset($credentials['totp'])) {
            $accessToken = $credentials['access_token'];
            $totpCode = $credentials['totp'];

            try {
                $payload = app('auth')->setToken($accessToken)->getPayload();
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
            } catch (\Exception $e) {
                return ['error' => 'Invalid or expired token'];
            }
        }

        // Invalid request
        return ['error' => 'Invalid login request'];
    }

    public function handleTotpLogin($accessToken, $totpCode)
    {
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
        $user = User::where('email', $email)->firstOrFail();
        $newPassword = 'welcome02';
        $user->update(['password' => app('hash')->make($newPassword)]);

        if (App::environment('local')) {
            Mail::to($email)->send(new ForgetPassword("{$user->first_name} {$user->last_name}", $newPassword));
        }

        return ['success' => true];
    }

    public function changePassword($user, $currentPassword, $newPassword)
    {
        if (!Hash::check($currentPassword, $user->password)) {
            return ['error' => 'Current password does not match'];
        }

        if (strcmp($currentPassword, $newPassword) === 0) {
            return ['error' => 'New password cannot be the same as current password'];
        }

        $user->password = app('hash')->make($newPassword);
        $user->save();
        return ['success' => true];
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        try {
            // Attempt to delete the user
            $user->delete();
        } catch (QueryException $e) {
            // Rethrow the exception for controller-level handling
            throw $e;
        }
    }


    public function updateUser($id, $data, $currentUserId, $currentUserRole)
    {
        $user = User::findOrFail($id);

        // Check if the current user is authorized
        if ($currentUserId !== $id && $currentUserRole !== 'admin') {
            throw new \Exception('You can only update your own data.');
        }

        // If 'role' is included in the data, ensure only admins can update it
        if (isset($data['role']) && $currentUserRole !== 'admin') {
            throw new \Exception('Only admins can update the role.');
        }

        // Remove 'role' field from the data if the user is not an admin
        if ($currentUserRole !== 'admin') {
            unset($data['role']);
        }

        // Exclude the password field from updates
        unset($data['password']);

        $success = $user->update($data);
        Cache::forget('auth.user.' . $user->id);

        return ['success' => (bool)$success];
    }

    public function searchUsers($query)
    {
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
        if ($user->failed_login_attempts < self::MAX_LOGIN_ATTEMPTS) {
            $user->increment('failed_login_attempts');
        }
    }

    private function resetLoginAttempts($user)
    {
        $user->update(['failed_login_attempts' => 0]);
    }

    public function getAuthenticatedUser()
    {
        return Auth::user();
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return ['message' => 'Successfully logged out'];
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return ['error' => 'Failed to logout, please try again.'];
        }
    }

    public function refreshToken()
    {
        return app('auth')->refresh(true, false);
    }

    public function getUserById($id, $currentUserId, $currentUserRole)
    {
        if ($currentUserRole === "admin") {
            return User::findOrFail($id);
        }
        if ($currentUserId == $id) {
            return User::findOrFail($id);
        }
        throw new \Exception('You are not authorized to view this user.');
    }

    public function patchUser($id, $data, $currentUserId, $currentUserRole)
    {
        $user = User::findOrFail($id);

        // Check if the current user is the same as the one being updated or is an admin
        if ($currentUserId === $id || $currentUserRole === "admin") {
            if (isset($data['role']) && $currentUserRole !== "admin") {
                throw new \Exception('Only admins can update the role.');
            }

            // Remove 'role' field for non-admin users
            if ($currentUserRole !== "admin") {
                unset($data['role']);
            }

            $user->update($data);
            Cache::forget('auth.user.' . $user->id);
            return ['success' => true];
        }

        throw new \Exception('You can only update your own data.');
    }

}
