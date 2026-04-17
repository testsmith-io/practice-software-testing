<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Http\Controllers;

use App\Http\Requests\Brand\DestroyBrand;
use App\Http\Requests\Brand\StoreBrand;
use App\Http\Requests\Brand\UpdateBrand;
use App\Models\Brand;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class BrandController extends Controller
{

    /**
     * @OA\Get(
     *      path="/brands",
     *      operationId="getBrands",
     *      tags={"Brand"},
     *      summary="Retrieve all brands",
     *      description="Retrieve all brands",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          headers={
     *              @OA\Header(header="Cache-Control", description="public, max-age=120", @OA\Schema(type="string")),
     *              @OA\Header(header="ETag", @OA\Schema(type="string"))
     *          },
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/BrandResponse")
     *          )
     *       ),
     *       @OA\Response(response=304, description="Not Modified"),
     * )
     */
    public function index()
    {
        return $this->preferredFormat(Brand::all());
    }

    /**
     * @OA\Post(
     *      path="/brands",
     *      operationId="storeBrand",
     *      tags={"Brand"},
     *      summary="Store new brand",
     *      description="Store new brand",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Brand request object",
     *          @OA\JsonContent(ref="#/components/schemas/BrandRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/BrandResponse")
     *      ),
     *      @OA\Response(response="409", ref="#/components/responses/DuplicateConflictResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     *      @OA\Response(response="500", ref="#/components/responses/InternalServerErrorResponse"),
     * )
     */
    public function store(StoreBrand $request)
    {
        return $this->preferredFormat(Brand::create($request->validated()), ResponseAlias::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *      path="/brands/{id}",
     *      operationId="getBrand",
     *      tags={"Brand"},
     *      summary="Retrieve specific brand",
     *      description="Retrieve specific brand",
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
     *          @OA\JsonContent(ref="#/components/schemas/BrandResponse")
     *       ),
     *       @OA\Response(response=304, description="Not Modified"),
     *       @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     * )
     */
    public function show($id)
    {
        return $this->preferredFormat(Brand::findOrFail($id));
    }

    /**
     * @OA\Put(
     *      path="/brands/{id}",
     *      operationId="updateBrand",
     *      tags={"Brand"},
     *      summary="Update specific brand",
     *      description="Update specific brand",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="The id parameter in path",
     *          required=true,
     *          example=1,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Brand request object",
     *          @OA\JsonContent(ref="#/components/schemas/BrandRequest")
     *      ),
     *      @OA\Response(response="200", ref="#/components/responses/UpdateResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="409", ref="#/components/responses/DuplicateConflictResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     *      @OA\Response(response="500", ref="#/components/responses/InternalServerErrorResponse"),
     * )
     */
    public function update(UpdateBrand $request, $id)
    {
        Brand::findOrFail($id)->update($request->validated());
        return $this->preferredFormat(['success' => true], ResponseAlias::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *      path="/brands/{id}",
     *      operationId="deleteBrand",
     *      tags={"Brand"},
     *      summary="Delete specific brand",
     *      description="",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="The id parameter in path",
     *          required=true,
     *          example=1,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response=204, description="Successful operation"),
     *      @OA\Response(response="409", ref="#/components/responses/ConflictResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     * ),
     */
    public function destroy(DestroyBrand $request, $id)
    {
        try {
            Brand::findOrFail($id)->delete();
            return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return $this->preferredFormat([
                    'success' => false,
                    'message' => 'Seems like this brand is used elsewhere.',
                ], ResponseAlias::HTTP_CONFLICT);
            } else {
                throw $e;
            }
        }
    }
}
