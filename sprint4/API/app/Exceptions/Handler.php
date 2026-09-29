<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

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
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $e
     * @return Response|JsonResponse
     *
     * @throws Throwable
     */
    /**
     * Simple exception-type -> [message, HTTP status] mappings that all render
     * as a JSON message body. Order matters: the first matching type wins.
     */
    private const SIMPLE_ERROR_RESPONSES = [
        TokenExpiredException::class => ['Token has expired and can no longer be refreshed', ResponseAlias::HTTP_UNAUTHORIZED],
        MethodNotAllowedHttpException::class => ['Method is not allowed for the requested route', ResponseAlias::HTTP_METHOD_NOT_ALLOWED],
        TokenBlacklistedException::class => ['Token is not valid', ResponseAlias::HTTP_UNAUTHORIZED],
        NotFoundHttpException::class => ['Resource not found', ResponseAlias::HTTP_NOT_FOUND],
        ModelNotFoundException::class => ['Requested item not found', ResponseAlias::HTTP_NOT_FOUND],
    ];

    public function render($request, Throwable $e)
    {
        foreach (self::SIMPLE_ERROR_RESPONSES as $type => [$message, $status]) {
            if ($e instanceof $type) {
                return response()->json(['message' => $message], $status);
            }
        }

        if ($e instanceof QueryException) {
            return match ($e->errorInfo[1]) {
                1062 => response(['message' => 'Duplicate Entry'], ResponseAlias::HTTP_CONFLICT),
                1364 => response(['message' => 'Something went wrong'], ResponseAlias::HTTP_NOT_FOUND),
                default => response()->json(['message' => 'Something went wrong'], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR),
            };
        }

        return parent::render($request, $e);
    }
}
