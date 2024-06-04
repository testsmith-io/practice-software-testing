<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\DestroyProduct;
use App\Http\Requests\Product\StoreProduct;
use App\Http\Requests\Product\UpdateProduct;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ProductController extends Controller {

    public function __construct() {
        $this->middleware('role:admin', ['only' => ['destroy']]);
    }

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
     *          description="Id of brand",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="by_category",
     *          in="query",
     *          description="Id of category",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="is_rental",
     *          in="query",
     *          description="Indication if we like to retrieve rentals products",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="between",
     *          in="query",
     *          description="Can be used to define a price range, like: price,10,30",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="sort",
     *          in="query",
     *          description="Can be used to sort based on specific column value, like: name,asc OR name,desc OR price,asc OR price,desc",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="pagenumber",
     *          required=false,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              title="PaginatedProductResponse",
     *              @OA\Property(property="current_page", type="integer", example=1),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/ProductResponse")
     *              ),
     *              @OA\Property(property="from", type="integer", example=1),
     *              @OA\Property(property="last_page", type="integer", example=1),
     *              @OA\Property(property="per_page", type="integer", example=1),
     *              @OA\Property(property="to", type="integer", example=1),
     *              @OA\Property(property="total", type="integer", example=1),
     *          )
     *      ),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     * )
     */
    public function index(Request $request) {
        if ($request->get('by_category') || $request->get('by_brand') || $request->get('by_category_slug') || $request->get('q')) {
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
            if ($byCategory = $request->get('by_category')) {
                $query->whereIn('category_id', explode(',', $byCategory));
            }

            if ($byBrand = $request->get('by_brand')) {
                $query->whereIn('brand_id', explode(',', $byBrand));
            }

            if ($q = $request->get('q')) {
                $query->where('name', 'like', "%$q%");
            }

            $isRental = $request->get('is_rental') ? 1 : 0;
            $query->where('is_rental', '=', $isRental);

            $results = $query->filter()->paginate(9);

            return $this->preferredFormat($results);
        } else {
            return $this->preferredFormat(Product::where('is_rental', $request->get('is_rental') ? 1 : 0)->with('product_image', 'category', 'brand')->filter()->paginate(9));
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
     *              @OA\Property(property="price", type="number", example=9.99),
     *              @OA\Property(property="is_location_offer", type="boolean", example=1),
     *              @OA\Property(property="is_rental", type="boolean", example=0),
     *              @OA\Property(property="is_stock", type="boolean", example=0),
     *          )
     *      ),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     * )
     */
    public function store(StoreProduct $request) {
        return $this->preferredFormat(Product::create($request->all()), ResponseAlias::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *      path="/products/{productId}",
     *      operationId="getProduct",
     *      tags={"Product"},
     *      summary="Retrieve specific product",
     *      description="Retrieve specific product",
     *      @OA\Parameter(
     *          name="productId",
     *          in="path",
     *          example=1,
     *          description="The productId parameter in path",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/ProductResponse")
     *      ),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     * )
     */
    public function show($id) {
        return $this->preferredFormat(Product::with('product_image', 'category', 'brand')->findOrFail($id));
    }

    /**
     * @OA\Get(
     *      path="/products/{productId}/related",
     *      operationId="getRelatedProducts",
     *      tags={"Product"},
     *      summary="Retrieve related products",
     *      description="Retrieve related products",
     *      @OA\Parameter(
     *          name="productId",
     *          in="path",
     *          example=1,
     *          description="The productId parameter in path",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/ProductResponse")
     *          ),
     *      ),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     * )
     */
    public function showRelated($id) {
        $categoryId = Product::where('id', $id)->first()->category_id;

        return $this->preferredFormat(Product::with('product_image', 'category', 'brand')->where('category_id', $categoryId)->where('id', '!=', $id)->get());
    }

    /**
     * @OA\Get(
     *      path="/products/search",
     *      operationId="searchProduct",
     *      tags={"Product"},
     *      summary="Retrieve specific products matching the search query",
     *      description="Search is performed on the `name` column",
     *      @OA\Parameter(
     *          name="q",
     *          in="query",
     *          description="A query phrase",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="pagenumber",
     *          required=false,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              title="PaginatedProductResponse",
     *              @OA\Property(property="current_page", type="integer", example=1),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/ProductResponse")
     *              ),
     *              @OA\Property(property="from", type="integer", example=1),
     *              @OA\Property(property="last_page", type="integer", example=1),
     *              @OA\Property(property="per_page", type="integer", example=1),
     *              @OA\Property(property="to", type="integer", example=1),
     *              @OA\Property(property="total", type="integer", example=1),
     *          )
     *      ),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     * )
     */
    public function search(Request $request) {
        $q = $request->get('q');

        return $this->preferredFormat(Product::with('product_image')->where('name', 'like', "%$q%")->paginate(9));
    }

    /**
     * @OA\Put(
     *      path="/products/{productId}",
     *      operationId="updateProduct",
     *      tags={"Product"},
     *      summary="Update specific product",
     *      description="Update specific product",
     *      @OA\Parameter(
     *          name="productId",
     *          in="path",
     *          description="The productId parameter in path",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Product request object",
     *          @OA\JsonContent(ref="#/components/schemas/ProductRequest")
     *      ),
     *      @OA\Response(response="200", ref="#/components/responses/UpdateResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     * )
     */
    public function update(UpdateProduct $request, $id) {
        return $this->preferredFormat(['success' => (bool)Product::where('id', $id)->update($request->all())], ResponseAlias::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *      path="/products/{productId}",
     *      operationId="deleteProduct",
     *      tags={"Product"},
     *      summary="Delete specific product",
     *      description="Admin role is required to delete a specific product",
     *      @OA\Parameter(
     *          name="productId",
     *          in="path",
     *          description="The productId parameter in path",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(response=204, description="Successful operation"),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="409", ref="#/components/responses/ConflictResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     *      security={{ "apiAuth": {} }}
     * ),
     */
    public function destroy(DestroyProduct $request, $id) {
        try {
            Product::find($id)->delete();
            return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return $this->preferredFormat([
                    'success' => false,
                    'message' => 'Seems like this product is used elsewhere.',
                ], ResponseAlias::HTTP_CONFLICT);
            }
        }
    }
}
