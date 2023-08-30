<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaginateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);
            unset($data['links'], $data['meta'], $data['last_page_url'], $data['next_page_url'], $data['prev_page_url'], $data['path']);
            $response = $response->setData($data);
        }
        return $response;
    }
}
