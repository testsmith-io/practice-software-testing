<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Http\Controllers;

use App\Http\Requests\Product\DestroyProduct;
use App\Http\Requests\Product\StoreProduct;
use App\Http\Requests\Product\UpdateProduct;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ProductController extends Controller
{

    /**
     * @OA\Get(
     *      path="/products",
     *      operationId="getProducts",
     *      tags={"Product"},
     *      summary="Retrieve all products",
     *      description="Retrieve all products",
     *      @OA\Parameter(
     *          name="by_brand",
     *          in="query",
     *          description="Comma-separated list of brand ids",
     *          required=false,
     *          @OA\Schema(type="string", description="Comma-separated list of ids, e.g. 1,2,3", example="1,2,3")
     *      ),
     *      @OA\Parameter(
     *          name="by_category",
     *          in="query",
     *          description="Comma-separated list of category ids",
     *          required=false,
     *          @OA\Schema(type="string", description="Comma-separated list of ids, e.g. 1,2,3", example="1,2,3")
     *      ),
     *      @OA\Parameter(
     *          name="by_category_slug",
     *          in="query",
     *          description="Filter products by category slug. Matches the given slug and includes all descendants.",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          headers={
     *              @OA\Header(header="Cache-Control", description="public, max-age=120", @OA\Schema(type="string")),
     *              @OA\Header(header="ETag", @OA\Schema(type="string"))
     *          },
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/ProductResponse")
     *          )
     *       ),
     *       @OA\Response(response=304, description="Not Modified"),
     *  )
     */
    public function index(Request $request)
    {
        if ($request->get('by_category') || $request->get('by_brand') || $request->get('by_category_slug')) {
            $query = Product::with('product_image', 'category', 'brand');
            if ($request->get('by_category_slug')) {
                $ids = DB::table('categories')->select('id')
                    ->from('categories')
                    ->whereIn('parent_id', function ($query) use ($request) {
                        $query->select('id')
                            ->from('categories')
                            ->where('slug', '=', $request->get('by_category_slug'));
                    });
                $query->whereIn('category_id', $ids);
            }
            if ($request->get('by_category')) {
                $query->whereIn('category_id', explode(',', $request->get('by_category')));
            }
            if ($request->get('by_brand')) {
                $query->whereIn('brand_id', explode(',', $request->get('by_brand')));
            }
            $results = $query->get();
            return $this->preferredFormat($results);
        } else {
            return $this->preferredFormat(Product::with('product_image', 'category', 'brand')->get());
        }
    }

    /**
     * @OA\Post(
     *      path="/products",
     *      operationId="storeProduct",
     *      tags={"Product"},
     *      summary="Store new product",
     *      description="Store new product",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Product request object",
     *          @OA\JsonContent(ref="#/components/schemas/ProductRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Returns when product is created",
     *          @OA\JsonContent(
     *              title="StoreProductResponse",
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="name", type="string", example="Lorum ipsum"),
     *              @OA\Property(property="description", type="string", example="Lorum ipsum"),
     *              @OA\Property(property="price", type="number", example=9.99)
     *          )
     *       ),
     *       @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     *       @OA\Response(response="500", ref="#/components/responses/InternalServerErrorResponse"),
     *  )
     */
    public function store(StoreProduct $request)
    {
        return $this->preferredFormat(Product::create($request->validated()), ResponseAlias::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *      path="/products/{id}",
     *      operationId="getProduct",
     *      tags={"Product"},
     *      summary="Retrieve specific product",
     *      description="Retrieve specific product",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          example=1,
     *          description="The id parameter in path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          headers={
     *              @OA\Header(header="Cache-Control", description="public, max-age=120", @OA\Schema(type="string")),
     *              @OA\Header(header="ETag", @OA\Schema(type="string"))
     *          },
     *          @OA\JsonContent(ref="#/components/schemas/ProductResponse")
     *       ),
     *       @OA\Response(response=304, description="Not Modified"),
     *       @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *  )
     */
    public function show($id)
    {
        return $this->preferredFormat(Product::with('product_image', 'category', 'brand')->findOrFail($id));
    }

    /**
     * @OA\Get(
     *      path="/products/{id}/related",
     *      operationId="getRelatedProducts",
     *      tags={"Product"},
     *      summary="Retrieve related products",
     *      description="Retrieve related products",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          example=1,
     *          description="The id parameter in path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          headers={
     *              @OA\Header(header="Cache-Control", description="public, max-age=120", @OA\Schema(type="string")),
     *              @OA\Header(header="ETag", @OA\Schema(type="string"))
     *          },
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/ProductResponse")
     *          ),
     *       ),
     *       @OA\Response(response=304, description="Not Modified"),
     *       @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *       @OA\Response(response="500", ref="#/components/responses/InternalServerErrorResponse"),
     *  )
     */
    public function showRelated($id)
    {
        $categoryId = Product::findOrFail($id)->category_id;

        return $this->preferredFormat(Product::with('product_image', 'category', 'brand')->where('category_id', $categoryId)->where('id', '!=', $id)->get());
    }

    /**
     * @OA\Put(
     *      path="/products/{id}",
     *      operationId="updateProduct",
     *      tags={"Product"},
     *      summary="Update specific product",
     *      description="Update specific product",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="The id parameter in path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Product request object",
     *          @OA\JsonContent(ref="#/components/schemas/ProductRequest")
     *      ),
     *      @OA\Response(response="200", ref="#/components/responses/UpdateResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     *      @OA\Response(response="500", ref="#/components/responses/InternalServerErrorResponse"),
     * )
     */
    public function update(UpdateProduct $request, $id)
    {
        Product::findOrFail($id)->update($request->validated());
        return $this->preferredFormat(['success' => true], ResponseAlias::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *      path="/products/{id}",
     *      operationId="deleteProduct",
     *      tags={"Product"},
     *      summary="Delete specific product",
     *      description="",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="The id parameter in path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response=204, description="Successful operation"),
     *      @OA\Response(response="409", ref="#/components/responses/ConflictResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     * ),
     */
    public function destroy(DestroyProduct $request, $id)
    {
        try {
            Product::findOrFail($id)->delete();
            return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return $this->preferredFormat([
                    'success' => false,
                    'message' => 'Seems like this product is used elsewhere.',
                ], ResponseAlias::HTTP_CONFLICT);
            } else {
                throw $e;
            }
        }
    }
}
