<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Http\Controllers;

use App\Services\Postcode\PostcodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class PostcodeController extends Controller
{
    public function __construct(private readonly PostcodeService $postcodeService)
    {
    }

    /**
     * @OA\Get(
     *      path="/postcode-lookup",
     *      operationId="postcodeLookup",
     *      tags={"Postcode"},
     *      summary="Lookup address details by postcode",
     *      description="Returns street, city and state for a given country/postcode/house number. The underlying source is configurable: a local faker driver (default) or an external HTTP service (e.g. a WireMock stub) when POSTCODE_LOOKUP_DRIVER=http.",
     *      @OA\Parameter(name="country", in="query", required=true, @OA\Schema(type="string", maxLength=40)),
     *      @OA\Parameter(name="postcode", in="query", required=true, @OA\Schema(type="string", maxLength=10)),
     *      @OA\Parameter(name="house_number", in="query", required=false, @OA\Schema(type="string", maxLength=10)),
     *      @OA\Response(
     *          response=200,
     *          description="Address details",
     *          @OA\JsonContent(
     *              @OA\Property(property="street", type="string"),
     *              @OA\Property(property="house_number", type="string"),
     *              @OA\Property(property="city", type="string"),
     *              @OA\Property(property="state", type="string"),
     *              @OA\Property(property="country", type="string"),
     *              @OA\Property(property="postcode", type="string"),
     *          )
     *      ),
     *      @OA\Response(response=422, ref="#/components/responses/UnprocessableEntityResponse"),
     *      @OA\Response(response=502, description="Upstream lookup failure"),
     * )
     */
    public function lookup(Request $request): JsonResponse
    {
        $data = $request->validate([
            'country' => ['required', 'string', 'max:40'],
            'postcode' => ['required', 'string', 'max:10'],
            'house_number' => ['nullable', 'string', 'max:10'],
        ]);

        // Runtime override via the admin UI (localStorage + header). Accept only
        // outside production to avoid turning the endpoint into an SSRF vector
        // on the public site.
        $overrideUrl = null;
        if (!App::environment('production')) {
            $headerUrl = $request->header('X-Postcode-Lookup-Url');
            if (is_string($headerUrl) && filter_var($headerUrl, FILTER_VALIDATE_URL)) {
                $overrideUrl = $headerUrl;
            }
        }

        try {
            $result = $this->postcodeService->lookup(
                $data['country'],
                $data['postcode'],
                $data['house_number'] ?? null,
                $overrideUrl,
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], ResponseAlias::HTTP_BAD_GATEWAY);
        }

        return response()->json($result->toArray());
    }
}
