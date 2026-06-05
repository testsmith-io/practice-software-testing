<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Http\Controllers;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * @OA\Info(
 *   title="Toolshop API",
 *   version="5.0.0",
 *   description="Toolshop REST API technical description",
 *   @OA\Contact(
 *     email="info@testsmith.io",
 *     name="Testsmith"
 *   )
 * )
 * @OA\Server(
 *     description="Deployed environment",
 *     url="https://api.practicesoftwaretesting.com"
 * )
 * @OA\Server(
 *     description="Local environment",
 *     url="http://localhost:8091"
 * )
 *
 * @OA\Components(
 *     @OA\Response(
 *         response="UpdateResponse",
 *         description="Result of the update",
 *         @OA\JsonContent(
 *             title="UpdateResponse",
 *             @OA\Property(property="success", type="boolean", example=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response="UnauthorizedResponse",
 *         description="Returns when user is not authenticated",
 *         @OA\JsonContent(
 *             title="UnauthorizedResponse",
 *             @OA\Property(property="message", type="string", example="Unauthorized")
 *         )
 *     ),
 *     @OA\Response(
 *         response="ItemNotFoundResponse",
 *         description="Returns when the resource is not found",
 *         @OA\JsonContent(
 *             title="ItemNotFoundResponse",
 *             @OA\Property(property="message", type="string", example="Requested item not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response="ResourceNotFoundResponse",
 *         description="Returns when the resource is not found",
 *         @OA\JsonContent(
 *             title="ResourceNotFoundResponse",
 *             @OA\Property(property="message", type="string", example="Resource not found"),
 *         )
 *     ),
 *     @OA\Response(
 *         response="ConflictResponse",
 *         description="Returns when the entity is used elsewhere"
 *     ),
 *     @OA\Response(
 *         response="MethodNotAllowedResponse",
 *         description="Returns when the method is not allowed for the requested route",
 *         @OA\JsonContent(
 *             title="MethodNotAllowedResponse",
 *             @OA\Property(property="message", type="string", example="Method is not allowed for the requested route")
 *         )
 *     ),
 *     @OA\Response(
 *         response="UnprocessableEntityResponse",
 *         description="Returns when the server was not able to process the content"
 *     ),
 *     @OA\Response(
 *         response="DuplicateConflictResponse",
 *         description="The resource conflicts with an existing one (e.g. unique slug already taken). Body is either a field-level MessageBag (when caught by validation) or a single message (when caught by the Handler from a race / FormRequest bypass).",
 *         @OA\JsonContent(
 *             oneOf={
 *                 @OA\Schema(
 *                     type="object",
 *                     description="Field-level conflict from the unique: validator rule",
 *                     additionalProperties={"type":"array","items":{"type":"string"}},
 *                     example={"slug":{"A brand already exists with this slug."}}
 *                 ),
 *                 @OA\Schema(
 *                     type="object",
 *                     description="Generic conflict from the global Handler (race / bypass)",
 *                     @OA\Property(property="message", type="string", example="Duplicate Entry")
 *                 )
 *             }
 *         )
 *     )
 * )
 */
class Controller extends BaseController
{
//    use AuthorizesRequests, ValidatesRequests;

//    protected function jsonResponse($data, $code = 200)
//    {
//        return response()->json($data, $code,
//            ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
//    }

    private function makeXML($xml, $status = 200, array $headers = [], $xmlRoot = 'response', $encoding = null)
    {
        if (is_array($xml)) {
            if (array_keys($xml) === range(0, count($xml) - 1)) {
                $xml = ['item' => $xml];
            }
            $xml = ArrayToXml::convert($xml, $xmlRoot, true, $encoding);
        } elseif (is_object($xml) && method_exists($xml, 'toArray')) {
            $arrayData = $xml->toArray();
            if (array_keys($arrayData) === range(0, count($arrayData) - 1)) {
                $arrayData = ['item' => $arrayData];
            }
            $xml = ArrayToXml::convert($arrayData, $xmlRoot, true, $encoding);
        } elseif (!is_string($xml)) {
            $xml = '';
        }

        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/xml';
        }

        return response($xml, $status)->withHeaders($headers);
    }

    protected function preferredFormat($data, $status = 200, array $headers = [], $xmlRoot = 'response'): Application|Response|JsonResponse|\Illuminate\Contracts\Foundation\Application|ResponseFactory
    {
        if (str_contains(app('request')->headers->get('Accept'), 'text/xml')) {
            return $this->makeXML($data, $status, array_merge($headers, ['Content-Type' => app('request')->headers->get('Accept')]), $xmlRoot);
        } else {
            return response()->json($data, $status,
                ['Content-Type' => 'application/json'], JSON_UNESCAPED_UNICODE
            );
        }
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Contracts\Foundation\Application|ResponseFactory|Application|JsonResponse|Response
     */
    protected function respondWithToken(string $token)
    {
        return $this->preferredFormat([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => app('auth')->factory()->getTTL() * 60
        ]);
    }

}
