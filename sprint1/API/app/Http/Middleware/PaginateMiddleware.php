<?php

namespace App\Http\Middleware;

use Closure;
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
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        if (strcmp(app('request')->headers->get('Accept'), 'xml') != 0) {
            $data = $response->getData(true);

            if (isset($data['links'])) {
                unset($data['links']);
            }
            if (isset($data['meta'], $data['meta']['links'])) {
                unset($data['meta']['links']);
            }

            $response->setData($data);
        }

        return $response;
    }
}
