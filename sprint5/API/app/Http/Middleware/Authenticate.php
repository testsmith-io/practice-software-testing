<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;
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
        if ($this->auth->guard($guard)->guest()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], ResponseAlias::HTTP_UNAUTHORIZED);
        }
        // Get the authenticated user
        $user = $this->auth->guard($guard)->user();

        // Check if the user is enabled
        if (!$user->enabled) {
            return response()->json([
                'message' => 'Account disabled.'
            ], ResponseAlias::HTTP_FORBIDDEN);
        }

        // Check for "restricted" token claim
        try {
            $token = JWTAuth::getToken();
            if ($token) {
                $payload = JWTAuth::getPayload($token);

                // If the token is restricted, block it
                if ($payload->get('restricted', false)) {
                    return response()->json([
                        'message' => 'Unauthorized token usage'
                    ], ResponseAlias::HTTP_UNAUTHORIZED);
                }
            }
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token error: ' . $e->getMessage()
            ], ResponseAlias::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
