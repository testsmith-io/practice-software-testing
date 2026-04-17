<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Http\Controllers;

use App\Http\Requests\Category\DestroyCategory;
use App\Http\Requests\Category\StoreCategory;
use App\Http\Requests\Category\UpdateCategory;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CategoryController extends Controller
{

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
     *          description="Filter root-level categories by slug. Does not match sub-category slugs.",
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
     *              @OA\Items(ref="#/components/schemas/CategoryTreeResponse")
     *          )
     *      ),
     *      @OA\Response(response=304, description="Not Modified"),
     * )
     */
    public function indexTree(Request $request)
    {
        if ($request->get('by_category_slug')) {
            return $this->preferredFormat(Category::with('sub_categories')->where("parent_id", "=", null)->where('slug', '=', $request->get('by_category_slug'))->get());
        } else {
            return $this->preferredFormat(Category::with('sub_categories')->where("parent_id", "=", null)->get());
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
     *          headers={
     *              @OA\Header(header="Cache-Control", description="public, max-age=120", @OA\Schema(type="string")),
     *              @OA\Header(header="ETag", @OA\Schema(type="string"))
     *          },
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/CategoryResponse")
     *          )
     *      ),
     *      @OA\Response(response=304, description="Not Modified"),
     * )
     */
    public function index()
    {
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
     *      @OA\Response(response="409", ref="#/components/responses/DuplicateConflictResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     *      @OA\Response(response="500", ref="#/components/responses/InternalServerErrorResponse"),
     * )
     */
    public function store(StoreCategory $request)
    {
        return $this->preferredFormat(Category::create($request->validated()), ResponseAlias::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *      path="/categories/tree/{id}",
     *      operationId="getCategory",
     *      tags={"Category"},
     *      summary="Retrieve specific category (including subcategories)",
     *      description="Retrieve specific category (including subcategories)",
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
     *          @OA\JsonContent(ref="#/components/schemas/CategoryTreeResponse")
     *      ),
     *      @OA\Response(response=304, description="Not Modified"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     * )
     */
    public function show($id)
    {
        return $this->preferredFormat(Category::with('sub_categories')->findOrFail($id));
    }

    /**
     * @OA\Put(
     *      path="/categories/{id}",
     *      operationId="updateCategory",
     *      tags={"Category"},
     *      summary="Update specific category",
     *      description="Update specific category",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          example=1,
     *          description="The id parameter in path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Category request object",
     *          @OA\JsonContent(ref="#/components/schemas/CategoryRequest")
     *      ),
     *      @OA\Response(response="200", ref="#/components/responses/UpdateResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="409", ref="#/components/responses/DuplicateConflictResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     *      @OA\Response(response="500", ref="#/components/responses/InternalServerErrorResponse"),
     * )
     */
    public function update(UpdateCategory $request, $id)
    {
        Category::findOrFail($id)->update($request->validated());
        return $this->preferredFormat(['success' => true], ResponseAlias::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *      path="/categories/{id}",
     *      operationId="deleteCategory",
     *      tags={"Category"},
     *      summary="Delete specific category",
     *      description="",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          example=1,
     *          description="The id parameter in path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response=204, description="Successful operation"),
     *      @OA\Response(response="409", ref="#/components/responses/ConflictResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     * ),
     */
    public function destroy(DestroyCategory $request, $id)
    {
        try {
            Category::findOrFail($id)->delete();
            return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return $this->preferredFormat([
                    'success' => false,
                    'message' => 'Seems like this category is used elsewhere.',
                ], ResponseAlias::HTTP_CONFLICT);
            } else {
                throw $e;
            }
        }
    }
}
