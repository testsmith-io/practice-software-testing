<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\SecurityScheme(
 *     type="http",
 *     description="Login with email and password to get the authentication token",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="apiAuth",
 * )
 */
class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var Auth
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param Auth $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $guard = null): mixed
    {
        // Resolve the user via JWT payload + cache so we don't hit the DB on
        // every authenticated request. The token signature is still verified
        // by JWTAuth::parseToken()->check(), but the User row itself comes
        // from cache.
        try {
            $token = JWTAuth::parseToken();
            $payload = $token->getPayload();

            $userId = $payload->get('sub');
            if (!$userId) {
                return response()->json(['message' => 'Unauthorized'], ResponseAlias::HTTP_UNAUTHORIZED);
            }

            $user = Cache::remember(
                'auth.user.' . $userId,
                60, // 60 seconds — short enough to honor account changes, long enough to avoid DB hits
                fn() => User::find($userId)
            );

            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], ResponseAlias::HTTP_UNAUTHORIZED);
            }

            // Bind the resolved user onto the guard so downstream code that
            // calls Auth::user() gets the cached instance instead of hitting DB.
            $this->auth->guard($guard)->setUser($user);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Unauthorized'
            ], ResponseAlias::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
