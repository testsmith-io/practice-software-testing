<?php

namespace App\Exceptions;

use Exception;
use HttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
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
        // Log all unhandled exceptions with full context
        Log::debug('Exception caught', [
            'type' => get_class($e),
            'message' => $e->getMessage(),
            'url' => $request->fullUrl(),
            'input' => $request->all(),
        ]);

        if ($e instanceof TokenExpiredException) {
            Log::info('JWT token expired', ['url' => $request->fullUrl()]);
            return response()->json([
                'message' => 'Token has expired and can no longer be refreshed',
            ], ResponseAlias::HTTP_UNAUTHORIZED);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            Log::warning('Method not allowed', ['method' => $request->method(), 'url' => $request->fullUrl()]);
            return response()->json([
                'message' => 'Method is not allowed for the requested route',
            ], ResponseAlias::HTTP_METHOD_NOT_ALLOWED);
        }

        if ($e instanceof TokenBlacklistedException) {
            Log::info('Blacklisted JWT token attempt', ['url' => $request->fullUrl()]);
            return response()->json([
                'message' => 'Token is not valid',
            ], ResponseAlias::HTTP_UNAUTHORIZED);
        }

        if ($e instanceof NotFoundHttpException) {
            Log::notice('Route not found', ['url' => $request->fullUrl()]);
            return response()->json([
                'message' => 'Resource not found'
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        if ($e instanceof ModelNotFoundException) {
            Log::notice('Model not found', ['url' => $request->fullUrl()]);
            return response()->json([
                'message' => 'Requested item not found'
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        if ($e instanceof QueryException) {
            $errorCode = $e->errorInfo[1];
            Log::error('Query exception', [
                'message' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'code' => $errorCode
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

        return parent::render($request, $e);
    }
}
