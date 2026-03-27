<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;

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
        if ($this->auth->guard($guard)->guest()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        // Get the authenticated user
        $user = $this->auth->guard($guard)->user();

        // Check if the user is enabled
        if (!$user->enabled) {
            return response()->json([
                'message' => 'Account disabled.'
            ], ResponseAlias::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
