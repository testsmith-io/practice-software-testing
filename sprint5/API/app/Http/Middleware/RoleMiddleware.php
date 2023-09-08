<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class RoleMiddleware
{
    /**
     * Run the request filter.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $role
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if ($request->user() === null) {
            return response()->json([
                'message' => 'Unauthorized'
            ], ResponseAlias::HTTP_UNAUTHORIZED);
        }
        if (strcmp($request->user()->role, $role) !== 0) {
            return response()->json([
                'message' => 'Forbidden'
            ], ResponseAlias::HTTP_FORBIDDEN);
        }
        return $next($request);
    }
}
