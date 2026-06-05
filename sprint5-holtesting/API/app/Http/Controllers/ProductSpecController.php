<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Http\Controllers;

use App\Models\ProductSpec;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ProductSpecController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:users')->except(['index', 'show', 'specNames']);
    }

    /**
     * @OA\Get(
     *      path="/products/{productId}/specs",
     *      operationId="getProductSpecs",
     *      tags={"Product Spec"},
     *      summary="Retrieve specs for a product",
     *      @OA\Parameter(name="productId", in="path", required=true, @OA\Schema(type="string")),
     *      @OA\Response(response=200, description="Successful operation",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ProductSpecResponse"))
     *      )
     * )
     */
    public function index($productId)
    {
        return $this->preferredFormat(ProductSpec::where('product_id', $productId)->get());
    }

    /**
     * @OA\Post(
     *      path="/products/{productId}/specs",
     *      operationId="storeProductSpec",
     *      tags={"Product Spec"},
     *      summary="Add a spec to a product",
     *      @OA\Parameter(name="productId", in="path", required=true, @OA\Schema(type="string")),
     *      @OA\RequestBody(required=true, @OA\JsonContent(
     *          required={"spec_name", "spec_value"},
     *          @OA\Property(property="spec_name", type="string", example="Weight"),
     *          @OA\Property(property="spec_value", type="string", example="1.5"),
     *          @OA\Property(property="spec_unit", type="string", example="kg", nullable=true)
     *      )),
     *      @OA\Response(response=201, description="Spec created",
     *          @OA\JsonContent(ref="#/components/schemas/ProductSpecResponse")
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function store(Request $request, $productId)
    {
        $request->validate([
            'spec_name' => 'required|string|max:100',
            'spec_value' => 'required|string|max:255',
            'spec_unit' => 'nullable|string|max:30',
        ]);

        $spec = ProductSpec::create([
            'product_id' => $productId,
            'spec_name' => $request->input('spec_name'),
            'spec_value' => $request->input('spec_value'),
            'spec_unit' => $request->input('spec_unit'),
        ]);

        $this->invalidateCache($productId);

        return $this->preferredFormat($spec, ResponseAlias::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *      path="/products/{productId}/specs/{specId}",
     *      operationId="getProductSpec",
     *      tags={"Product Spec"},
     *      summary="Retrieve a specific spec",
     *      @OA\Parameter(name="productId", in="path", required=true, @OA\Schema(type="string")),
     *      @OA\Parameter(name="specId", in="path", required=true, @OA\Schema(type="string")),
     *      @OA\Response(response=200, description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/ProductSpecResponse")
     *      )
     * )
     */
    public function show($productId, $specId)
    {
        return $this->preferredFormat(
            ProductSpec::where('product_id', $productId)->findOrFail($specId)
        );
    }

    /**
     * @OA\Put(
     *      path="/products/{productId}/specs/{specId}",
     *      operationId="updateProductSpec",
     *      tags={"Product Spec"},
     *      summary="Update a spec",
     *      @OA\Parameter(name="productId", in="path", required=true, @OA\Schema(type="string")),
     *      @OA\Parameter(name="specId", in="path", required=true, @OA\Schema(type="string")),
     *      @OA\RequestBody(required=true, @OA\JsonContent(
     *          @OA\Property(property="spec_name", type="string"),
     *          @OA\Property(property="spec_value", type="string"),
     *          @OA\Property(property="spec_unit", type="string", nullable=true)
     *      )),
     *      @OA\Response(response="200", ref="#/components/responses/UpdateResponse"),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function update(Request $request, $productId, $specId)
    {
        $request->validate([
            'spec_name' => 'sometimes|string|max:100',
            'spec_value' => 'sometimes|string|max:255',
            'spec_unit' => 'nullable|string|max:30',
        ]);

        $spec = ProductSpec::where('product_id', $productId)
            ->where('id', $specId)
            ->firstOrFail();
        $spec->update($request->only(['spec_name', 'spec_value', 'spec_unit']));

        $this->invalidateCache($productId);

        return $this->preferredFormat(['success' => true]);
    }

    /**
     * @OA\Delete(
     *      path="/products/{productId}/specs/{specId}",
     *      operationId="deleteProductSpec",
     *      tags={"Product Spec"},
     *      summary="Delete a spec",
     *      @OA\Parameter(name="productId", in="path", required=true, @OA\Schema(type="string")),
     *      @OA\Parameter(name="specId", in="path", required=true, @OA\Schema(type="string")),
     *      @OA\Response(response=204, description="Successful operation"),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function destroy($productId, $specId)
    {
        ProductSpec::where('product_id', $productId)->where('id', $specId)->delete();
        $this->invalidateCache($productId);
        return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
    }

    private function invalidateCache($productId): void
    {
        Cache::forget('product_specs.names');
        // Drop the cached product detail since it eager-loads specs.
        try {
            Cache::tags(['products'])->flush();
        } catch (\BadMethodCallException $e) {
            Cache::forget("products.{$productId}");
        }
    }

    /**
     * @OA\Get(
     *      path="/product-specs/names",
     *      operationId="getSpecNames",
     *      tags={"Product Spec"},
     *      summary="Retrieve all distinct spec names with their values",
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function specNames()
    {
        // The DB is reset hourly, so keep TTL well below that window.
        $specs = Cache::remember('product_specs.names', 300, function () {
            return DB::table('product_specs')
                ->select('spec_name', 'spec_value', 'spec_unit')
                ->distinct()
                ->orderBy('spec_name')
                ->orderBy('spec_value')
                ->get()
                ->groupBy('spec_name')
                ->map(function ($values, $name) {
                    return [
                        'name' => $name,
                        'values' => $values->pluck('spec_value')->unique()->values(),
                        'unit' => $values->first()->spec_unit,
                    ];
                })
                ->values();
        });

        return $this->preferredFormat($specs);
    }
}
