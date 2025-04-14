<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\DestroyProduct;
use App\Http\Requests\Product\StoreProduct;
use App\Http\Requests\Product\UpdateProduct;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
     *          description="Id of brand",
     *          required=false,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="by_category",
     *          in="query",
     *          description="Id of category",
     *          required=false,
     *          @OA\Schema(type="integer")
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
     *              @OA\Property(property="next_page_url", type="integer", example=1),
     *              @OA\Property(property="path", type="integer", example=1),
     *              @OA\Property(property="per_page", type="integer", example=1),
     *              @OA\Property(property="prev_page_url", type="integer", example=1),
     *              @OA\Property(property="to", type="integer", example=1),
     *              @OA\Property(property="total", type="integer", example=1),
     *          )
     *      ),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *  )
     */
    public function index(Request $request)
    {
        Log::info('Fetching products', $request->all());

        if ($request->get('by_category') || $request->get('by_brand') || $request->get('by_category_slug') || $request->get('q')) {
            Log::debug('Building filtered product query');
            $query = Product::with('product_image', 'category', 'brand');

            if ($request->get('by_category_slug')) {
                Log::debug('Filtering by category_slug', ['slug' => $request->get('by_category_slug')]);
                $ids = DB::table('categories')->select('id')
                    ->from('categories')
                    ->where('slug', '=', $request->get('by_category_slug'))
                    ->orWhereIn('parent_id', function ($query) use ($request) {
                        $query->select('id')
                            ->from('categories')
                            ->where('slug', '=', $request->get('by_category_slug'));
                    });
                $query->whereIn('category_id', $ids);
            }

            if ($request->get('by_category')) {
                Log::debug('Filtering by category', ['ids' => $request->get('by_category')]);
                $query->whereIn('category_id', explode(',', $request->get('by_category')));
            }

            if ($request->get('by_brand')) {
                Log::debug('Filtering by brand', ['ids' => $request->get('by_brand')]);
                $query->whereIn('brand_id', explode(',', $request->get('by_brand')));
            }

            if ($request->get('is_rental')) {
                Log::debug('Filtering by rental flag', ['is_rental' => $request->get('is_rental')]);
                $query->where('is_rental', '=', $request->get('is_rental') ? 1 : 0);
            }

            if ($request->get('q')) {
                $q = $request->get('q');
                Log::debug('Searching by name', ['query' => $q]);
                $query->where('name', 'like', "%$q%");
            }

            $results = $query->filter()->paginate(9);
            Log::info('Filtered product results returned');
            return $this->preferredFormat($results);
        }

        Log::debug('Fetching products without filters');
        $results = Product::where('is_rental', $request->get('is_rental') ? 1 : 0)
            ->with('product_image', 'category', 'brand')
            ->filter()
            ->paginate(9);

        return $this->preferredFormat($results);
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
     *          description="Returns when user is not authenticated",
     *          @OA\JsonContent(
     *              title="StoreProductResponse",
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="name", type="string", example="Lorum ipsum"),
     *              @OA\Property(property="description", type="string", example="Lorum ipsum"),
     *              @OA\Property(property="price", type="number", example=9.99),
     *              @OA\Property(property="is_location_offer", type="boolean", example=1),
     *              @OA\Property(property="is_rental", type="boolean", example=0),
     *          )
     *      ),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     *  )
     */
    public function store(StoreProduct $request)
    {
        Log::info('Creating new product', $request->validated());

        $product = Product::create($request->all());

        Log::debug('Product created', ['id' => $product->id]);

        return $this->preferredFormat($product, ResponseAlias::HTTP_CREATED);
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
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/BrandResponse")
     *      ),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *  )
     */
    public function show($id)
    {
        Log::info('Fetching product by ID', ['id' => $id]);

        $product = Product::with('product_image', 'category', 'brand')->findOrFail($id);

        Log::debug('Product found', ['id' => $product->id]);

        return $this->preferredFormat($product);
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
     *          @OA\Schema(type="integer")
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
     *  )
     */
    public function showRelated($id)
    {
        Log::info('Fetching related products for product ID', ['id' => $id]);

        $categoryId = Product::where('id', $id)->first()->category_id;

        $related = Product::with('product_image', 'category', 'brand')
            ->where('category_id', $categoryId)
            ->where('id', '!=', $id)
            ->get();

        Log::debug('Related products retrieved', ['count' => $related->count()]);

        return $this->preferredFormat($related);
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
     *              @OA\Property(property="next_page_url", type="integer", example=1),
     *              @OA\Property(property="path", type="integer", example=1),
     *              @OA\Property(property="per_page", type="integer", example=1),
     *              @OA\Property(property="prev_page_url", type="integer", example=1),
     *              @OA\Property(property="to", type="integer", example=1),
     *              @OA\Property(property="total", type="integer", example=1),
     *          )
     *      ),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *  )
     */
    public function search(Request $request)
    {
        $q = $request->get('q');

        Log::info('Searching for products', ['query' => $q]);

        $results = Product::with('product_image')
            ->where('name', 'like', "%$q%")
            ->paginate(9);

        return $this->preferredFormat($results);
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
     *          @OA\Schema(type="integer")
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
     *  )
     */
    public function update(UpdateProduct $request, $id)
    {
        Log::info('Updating product', ['id' => $id, 'payload' => $request->validated()]);

        $success = Product::where('id', $id)->update($request->all());

        Log::debug('Product update result', ['success' => $success]);

        return $this->preferredFormat(['success' => (bool)$success], ResponseAlias::HTTP_OK);
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
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response=204, description="Successful operation"),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="409", ref="#/components/responses/ConflictResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function destroy(DestroyProduct $request, $id)
    {
        Log::info('Attempting to delete product', ['id' => $id]);

        try {
            Product::find($id)->delete();

            Log::debug('Product deleted successfully');

            return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
        } catch (QueryException $e) {
            Log::error('Failed to delete product', [
                'id' => $id,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            if ($e->getCode() === '23000') {
                return $this->preferredFormat([
                    'success' => false,
                    'message' => 'Seems like this product is used elsewhere.',
                ], ResponseAlias::HTTP_CONFLICT);
            }
        }
    }
}
