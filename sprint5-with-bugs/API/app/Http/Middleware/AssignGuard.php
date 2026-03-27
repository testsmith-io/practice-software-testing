<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AssignGuard
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = null): mixed
    {
        if ($guard != null)
            app('auth')->shouldUse($guard);
        return $next($request);
    }
}
