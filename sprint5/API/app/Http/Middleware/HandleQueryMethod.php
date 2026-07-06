<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles HTTP QUERY requests (RFC 10008): a safe, idempotent method whose
 * request content carries the query parameters that would otherwise be sent
 * as a URL query string.
 */
class HandleQueryMethod
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('QUERY')) {
            // RFC 10008: servers MUST fail the request if the Content-Type
            // field is missing or inconsistent with the request content.
            if (!$request->isJson()) {
                abort(Response::HTTP_UNSUPPORTED_MEDIA_TYPE, 'QUERY requests must use Content-Type: application/json');
            }

            // Merge the query document into the query bag so controllers read
            // QUERY content exactly like a GET query string.
            $request->query->add($request->json()->all());
        }

        $response = $next($request);

        // Advertise QUERY support and the accepted query format (RFC 10008).
        $response->headers->set('Accept-Query', 'application/json');

        return $response;
    }
}
