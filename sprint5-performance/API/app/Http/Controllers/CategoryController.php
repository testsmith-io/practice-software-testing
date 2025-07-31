<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\DestroyCategory;
use App\Http\Requests\Category\PatchCategory;
use App\Http\Requests\Category\StoreCategory;
use App\Http\Requests\Category\UpdateCategory;
use App\Services\CategoryService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CategoryController extends Controller
{

    private $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
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
     *              @OA\Items(ref="#/components/schemas/CategoryTreeResponse")
     *          )
     *      ),
     *      @OA\Response(response="404", ref="#/components/responses/ResourceNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     * )
     */
    public function indexTree(Request $request)
    {
        $byCategorySlug = $request->get('by_category_slug');
        $categories = $this->categoryService->getCategoriesTree($byCategorySlug);

        return $this->preferredFormat($categories);
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
     * )
     */
    public function index()
    {
        $categories = $this->categoryService->getAllCategories();
        return $this->preferredFormat($categories);
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
     * )
     */
    public function store(StoreCategory $request)
    {
        $category = $this->categoryService->createCategory($request->all());
        return $this->preferredFormat($category, ResponseAlias::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *      path="/categories/tree/{categoryId}",
     *      operationId="getCategory",
     *      tags={"Category"},
     *      summary="Retrieve specific category (including subcategories)",
     *      description="Retrieve specific category (including subcategories)",
     *      @OA\Parameter(
     *          name="categoryId",
     *          in="path",
     *          example=1,
     *          description="The categoryId parameter in path",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/CategoryTreeResponse")
     *      ),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     * )
     */
    public function show($id)
    {
        $category = $this->categoryService->getCategoryById($id);
        return $this->preferredFormat($category);
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
     * )
     */
    public function search(Request $request)
    {
        $q = $request->get('q');
        $categories = $this->categoryService->searchCategories($q);

        return $this->preferredFormat($categories);
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
     *          @OA\Schema(type="string")
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
     * )
     */
    public function update(UpdateCategory $request, $id)
    {
        $updated = $this->categoryService->updateCategory($id, $request->all());
        return $this->preferredFormat(['success' => (bool)$updated], ResponseAlias::HTTP_OK);
    }

    /**
     * @OA\Patch(
     *      path="/categories/{categoryId}",
     *      operationId="patchCategory",
     *      tags={"Category"},
     *      summary="Partially update specific category",
     *      description="Partially update specific category",
     *      @OA\Parameter(
     *          name="categoryId",
     *          in="path",
     *          example=1,
     *          description="The categoryId parameter in path",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Partial category request object. Only fields to be updated should be included.",
     *          @OA\JsonContent(ref="#/components/schemas/CategoryRequest")
     *      ),
     *      @OA\Response(response="200", ref="#/components/responses/UpdateResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ResourceNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     * )
     */
    public function patch(PatchCategory $request, $id)
    {
        $validatedData = $request->validated();
        $updated = $this->categoryService->updateCategory($id, $validatedData);

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
    public function destroy(DestroyCategory $request, $id)
    {
        try {
            $this->categoryService->deleteCategory($id);

            return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return $this->preferredFormat([
                    'success' => false,
                    'message' => 'Seems like this category is used elsewhere.',
                ], ResponseAlias::HTTP_CONFLICT);
            }
        }
    }
}
