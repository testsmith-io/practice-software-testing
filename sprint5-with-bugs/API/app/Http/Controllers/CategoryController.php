<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\DestroyCategory;
use App\Http\Requests\Category\StoreCategory;
use App\Http\Requests\Category\UpdateCategory;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin', ['only' => ['destroy']]);
    }

    /**
     * @OA\Get(
     *      path="/categories/tree",
     *      operationId="getCategoriesTree",
     *      tags={"Category"},
     *      summary="Retrieve all categories (including subcategories)",
     *      description="Retrieve all categories (including subcategories)",
     *      @OA\Parameter(
     *          name="by_category_slug",
     *          in="query",
     *          description="Parent category slug",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/CategoryResponse")
     *          )
     *      ),
     *      @OA\Response(response="404", ref="#/components/responses/ResourceNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *  )
     */
    public function indexTree(Request $request)
    {
        Log::debug('Fetching category tree', ['slug' => $request->get('by_category_slug')]);

        if ($request->get('by_category_slug')) {
            return $this->preferredFormat(
                Category::with('sub_categories')
                    ->where("parent_id", "=", null)
                    ->where('slug', '=', $request->get('by_category_slug'))
                    ->get()
            );
        } else {
            return $this->preferredFormat(
                Category::with('sub_categories')->where("parent_id", "=", null)->get()
            );
        }
    }

    /**
     * @OA\Get(
     *      path="/categories",
     *      operationId="getCategories",
     *      tags={"Category"},
     *      summary="Retrieve all categories",
     *      description="Retrieve all categories",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/CategoryResponse")
     *          )
     *      ),
     *      @OA\Response(response="404", ref="#/components/responses/ResourceNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *  )
     */
    public function index()
    {
        Log::debug('Fetching all categories');
        return $this->preferredFormat(Category::all());
    }

    /**
     * @OA\Post(
     *      path="/categories",
     *      operationId="storeCategory",
     *      tags={"Category"},
     *      summary="Store new category",
     *      description="Store new category",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Category request object",
     *          @OA\JsonContent(ref="#/components/schemas/CategoryRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/CategoryResponse")
     *      ),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     *  )
     */
    public function store(StoreCategory $request)
    {
        Log::info('Creating new category', ['data' => $request->only(['name', 'slug', 'parent_id'])]);

        $category = Category::create($request->all());

        Log::debug('Category created', ['id' => $category->id]);

        return $this->preferredFormat($category, ResponseAlias::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *      path="/categories/{categoryId}",
     *      operationId="getCategory",
     *      tags={"Category"},
     *      summary="Retrieve specific category",
     *      description="Retrieve specific category",
     *      @OA\Parameter(
     *          name="categoryId",
     *          in="path",
     *          example=1,
     *          description="The categoryId parameter in path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/CategoryResponse")
     *      ),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *  )
     */
    public function show($id)
    {
        Log::debug('Fetching category by ID', ['id' => $id]);
        return $this->preferredFormat(Category::with('sub_categories')->findOrFail($id));
    }

    /**
     * @OA\Get(
     *      path="/categories/search",
     *      operationId="searchCategory",
     *      tags={"Category"},
     *      summary="Retrieve specific categories matching the search query",
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
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/CategoryResponse")
     *          )
     *      ),
     *      @OA\Response(response="404", ref="#/components/responses/ResourceNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *  )
     */
    public function search(Request $request)
    {
        $q = $request->get('q');
        Log::debug('Searching categories', ['query' => $q]);

        return $this->preferredFormat(
            Category::with('sub_categories')->where('name', 'like', "%$q%")->get()
        );
    }

    /**
     * @OA\Put(
     *      path="/categories/{categoryId}",
     *      operationId="updateCategory",
     *      tags={"Category"},
     *      summary="Update specific category",
     *      description="Update specific category",
     *      @OA\Parameter(
     *          name="categoryId",
     *          in="path",
     *          example=1,
     *          description="The categoryId parameter in path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Category request object",
     *          @OA\JsonContent(ref="#/components/schemas/CategoryRequest")
     *      ),
     *      @OA\Response(response="200", ref="#/components/responses/UpdateResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ResourceNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     *  )
     */
    public function update(UpdateCategory $request, $id)
    {
        Log::info('Updating category', ['id' => $id, 'data' => $request->only(['name', 'slug', 'parent_id'])]);

        $updated = Category::where('id', $id)->update($request->all());

        Log::debug('Update result', ['success' => (bool)$updated]);

        return $this->preferredFormat(['success' => (bool)$updated], ResponseAlias::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *      path="/categories/{categoryId}",
     *      operationId="deleteCategory",
     *      tags={"Category"},
     *      summary="Delete specific category",
     *      description="Admin role is required to delete a specific category",
     *      @OA\Parameter(
     *          name="categoryId",
     *          in="path",
     *          example=1,
     *          description="The categoryId parameter in path",
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
    public function destroy(DestroyCategory $request, $id)
    {
        Log::warning('Attempting to delete category', ['id' => $id]);

        try {
            Category::find($id)->delete();

            Log::info('Category deleted', ['id' => $id]);
            return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);

        } catch (QueryException $e) {
            Log::error('Failed to delete category', [
                'id' => $id,
                'error_code' => $e->getCode(),
                'message' => $e->getMessage()
            ]);

            if ($e->getCode() === '23000') {
                return $this->preferredFormat([
                    'success' => false,
                    'message' => 'Seems like this category is used elsewhere.',
                ], ResponseAlias::HTTP_CONFLICT);
            }

            return $this->preferredFormat([
                'success' => false,
                'message' => 'Could not delete category.',
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
