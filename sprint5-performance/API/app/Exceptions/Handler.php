<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * @param Throwable $e
     * @return void
     *
     * @throws Exception|Throwable
     */
    public function report(Throwable $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $e
     * @return Response|JsonResponse
     *
     * @throws Throwable
     */
    public function render($request, Throwable $e)
    {
        $route = $request->path();
        $method = $request->method();
        $ip = $request->ip();

        if ($e instanceof TokenExpiredException) {
            Log::warning('TokenExpiredException', ['route' => $route, 'ip' => $ip]);
            return response()->json([
                'message' => 'Token has expired and can no longer be refreshed',
            ], ResponseAlias::HTTP_UNAUTHORIZED);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            Log::notice('MethodNotAllowedHttpException', ['method' => $method, 'route' => $route]);
            return response()->json([
                'message' => 'Method is not allowed for the requested route',
            ], ResponseAlias::HTTP_METHOD_NOT_ALLOWED);
        }

        if ($e instanceof TokenBlacklistedException) {
            Log::warning('TokenBlacklistedException', ['route' => $route, 'ip' => $ip]);
            return response()->json([
                'message' => 'Token is not valid',
            ], ResponseAlias::HTTP_UNAUTHORIZED);
        }

        if ($e instanceof NotFoundHttpException) {
            Log::info('NotFoundHttpException', ['route' => $route, 'method' => $method]);
            return response()->json([
                'message' => 'Resource not found'
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        if ($e instanceof ModelNotFoundException) {
            Log::info('ModelNotFoundException', ['route' => $route]);
            return response()->json([
                'message' => 'Requested item not found'
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        if ($e instanceof TooManyRequestsHttpException) {
            Log::notice('TooManyRequestsHttpException', ['ip' => $ip]);
            return response()->json([
                'message' => 'Too many requests'
            ], ResponseAlias::HTTP_TOO_MANY_REQUESTS);
        }

        if ($e instanceof HttpException && $e->getStatusCode() === 504) {
            return response()->json([
                'message' => 'Gateway Timeout'
            ], ResponseAlias::HTTP_GATEWAY_TIMEOUT);
        }

        if ($e instanceof QueryException) {
            $errorCode = $e->errorInfo[1] ?? null;
            Log::error('QueryException', [
                'route' => $route,
                'code' => $errorCode,
                'message' => $e->getMessage(),
            ]);

            return match ($errorCode) {
                1062 => response([
                    'message' => 'Duplicate Entry'
                ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY),
                1364 => response([
                    'message' => 'Something went wrong'
                ], ResponseAlias::HTTP_NOT_FOUND),
                default => response()->json([
                    'message' => 'Something went wrong'
                ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR),
            };
        }

        // Catch-all for uncaught exceptions
        Log::critical('Unhandled Exception', [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'route' => $route,
            'method' => $method,
            'ip' => $ip,
            'trace' => config('app.debug') ? $e->getTraceAsString() : null,
        ]);

        return parent::render($request, $e);
    }
}
