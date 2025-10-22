<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
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
 *     url="https://api-with-bugs.practicesoftwaretesting.com"
 * ),
 * @OA\Server(
 *     description="Local environment",
 *     url="http://localhost:8091"
 * )
 *
 * @OA\Components(
 *      @OA\Response(
 *          response="UpdateResponse",
 *          description="Result of the update",
 *          @OA\JsonContent(
 *              title="UpdateResponse",
 *              @OA\Property(property="success", type="boolean", example=true)
 *          )
 *      ),
 *      @OA\Response(
 *          response="UnauthorizedResponse",
 *          description="Returns when user is not authenticated",
 *          @OA\JsonContent(
 *              title="UnauthorizedResponse",
 *              @OA\Property(property="message", type="string", example="Unauthorized")
 *          )
 *      ),
 *      @OA\Response(
 *          response="ItemNotFoundResponse",
 *          description="Returns when the resource is not found",
 *          @OA\JsonContent(
 *              title="ItemNotFoundResponse",
 *              @OA\Property(property="message", type="string", example="Requested item not found")
 *          )
 *      ),
 *      @OA\Response(
 *          response="ResourceNotFoundResponse",
 *          description="Returns when the resource is not found",
 *          @OA\JsonContent(
 *              title="ResourceNotFoundResponse",
 *              @OA\Property(property="message", type="string", example="Resource not found"),
 *          )
 *      ),
 *      @OA\Response(
 *          response="ConflictResponse",
 *          description="Returns when the entity is used elsewhere"
 *      ),
 *      @OA\Response(
 *          response="MethodNotAllowedResponse",
 *          description="Returns when the method is not allowed for the requested route",
 *          @OA\JsonContent(
 *              title="MethodNotAllowedResponse",
 *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route")
 *          )
 *      ),
 *      @OA\Response(
 *          response="UnprocessableEntityResponse",
 *          description="Returns when the server was not able to process the content"
 *      )
 *  )
 */
class Controller extends BaseController
{

    private function makeXML($xml, $status = 200, array $headers = [], $xmlRoot = 'response', $encoding = null)
    {
        if (is_array($xml)) {
            // Check if array has numeric keys (sequential array)
            if (array_keys($xml) === range(0, count($xml) - 1)) {
                // Wrap sequential array in a container element
                $xml = ['item' => $xml];
            }
            $xml = ArrayToXml::convert($xml, $xmlRoot, true, $encoding);
        } elseif (is_object($xml) && method_exists($xml, 'toArray')) {
            $arrayData = $xml->toArray();
            // Check if array has numeric keys (sequential array)
            if (array_keys($arrayData) === range(0, count($arrayData) - 1)) {
                // Wrap sequential array in a container element
                $arrayData = ['item' => $arrayData];
            }
            $xml = ArrayToXml::convert($arrayData, $xmlRoot, true, $encoding);
        } elseif (is_string($xml)) {
            $xml = $xml;
        } else {
            $xml = '';
        }
        if (!isset($headers['Content-Type'])) {
            $headers = array_merge($headers, ['Content-Type' => 'application/xml']);
        }
        return response($xml, $status)->withHeaders($headers);
    }

    protected function preferredFormat($data, $status = 200, array $headers = [], $xmlRoot = 'response')
    {
        if (strcmp(app('request')->headers->get('Accept'), 'text/xml') == 0) {
            return $this->makeXML($data, $status, array_merge($headers, ['Content-Type' => app('request')->headers->get('Accept')]), $xmlRoot);
        } else {
            $defaultHeaders = ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'];
            return response()->json($data, $status,
                array_merge($defaultHeaders, $headers), JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return JsonResponse
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
