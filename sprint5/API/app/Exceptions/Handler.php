<?php

namespace App\Exceptions;

use Exception;
use HttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
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
        if ($e instanceof TokenExpiredException) {
            return response()->json([
                'message' => 'Token has expired and can no longer be refreshed',
            ], ResponseAlias::HTTP_UNAUTHORIZED);
        }
        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'message' => 'Method is not allowed for the requested route',
            ], ResponseAlias::HTTP_METHOD_NOT_ALLOWED);
        }
        if ($e instanceof TokenBlacklistedException) {
            return response()->json([
                'message' => 'Token is not valid',
            ], ResponseAlias::HTTP_UNAUTHORIZED);
        }
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'message' => 'Resource not found'
            ], ResponseAlias::HTTP_NOT_FOUND);
        }
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'message' => 'Requested item not found'
            ], ResponseAlias::HTTP_NOT_FOUND);
        }
        if ($e instanceof TooManyRequestsHttpException) {
            return response()->json([
                'message' => 'Too many requests'
            ], ResponseAlias::HTTP_TOO_MANY_REQUESTS);
        }
        if ($e instanceof QueryException) {
            $errorCode = $e->errorInfo[1];
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
