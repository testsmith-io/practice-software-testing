<?php

namespace App\Http\Controllers;

use App\Http\Requests\Brand\DestroyBrand;
use App\Http\Requests\Brand\PatchBrand;
use App\Http\Requests\Brand\StoreBrand;
use App\Http\Requests\Brand\UpdateBrand;
use App\Services\BrandService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class BrandController extends Controller
{
    private $brandService;

    public function __construct(BrandService $brandService)
    {
        $this->brandService = $brandService;
        $this->middleware('role:admin', ['only' => ['destroy']]);
    }

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
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/BrandResponse")
     *          )
     *       ),
     *       @OA\Response(response="404", ref="#/components/responses/ResourceNotFoundResponse"),
     *       @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     * )
     */
    public function index()
    {
        $brands = $this->brandService->getAllBrands();
        return $this->preferredFormat($brands);
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
     *       ),
     *       @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *       @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *       @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     * )
     */
    public function store(StoreBrand $request)
    {
        $brand = $this->brandService->createBrand($request->all());
        return $this->preferredFormat($brand, ResponseAlias::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *      path="/brands/{brandId}",
     *      operationId="getBrand",
     *      tags={"Brand"},
     *      summary="Retrieve specific brand",
     *      description="Retrieve specific brand",
     *      @OA\Parameter(
     *          name="brandId",
     *          in="path",
     *          example=1,
     *          description="The brandId parameter in path",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/BrandResponse")
     *       ),
     *       @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *       @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     * )
     */
    public function show($id)
    {
        $brand = $this->brandService->getBrandById($id);
        return $this->preferredFormat($brand);
    }

    /**
     * @OA\Get(
     *      path="/brands/search",
     *      operationId="searchBrand",
     *      tags={"Brand"},
     *      summary="Retrieve specific brands matching the search query",
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
     *              @OA\Items(ref="#/components/schemas/BrandResponse")
     *          )
     *       ),
     *       @OA\Response(response="404", ref="#/components/responses/ResourceNotFoundResponse"),
     *       @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     * )
     */
    public function search(Request $request)
    {
        $q = $request->get('q');
        $brands = $this->brandService->searchBrands($q);
        return $this->preferredFormat($brands);
    }

    /**
     * @OA\Put(
     *      path="/brands/{brandId}",
     *      operationId="updateBrand",
     *      tags={"Brand"},
     *      summary="Update specific brand",
     *      description="Update specific brand",
     *      @OA\Parameter(
     *          name="brandId",
     *          in="path",
     *          description="The brandId parameter in path",
     *          required=true,
     *          example=1,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Brand request object",
     *          @OA\JsonContent(ref="#/components/schemas/BrandRequest")
     *      ),
     *      @OA\Response(response="200", ref="#/components/responses/UpdateResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     * )
     */
    public function update(UpdateBrand $request, $id)
    {
        $updated = $this->brandService->updateBrand($id, $request->all());
        return $this->preferredFormat(['success' => (bool)$updated], ResponseAlias::HTTP_OK);
    }

    /**
     * @OA\Patch(
     *      path="/brands/{brandId}",
     *      operationId="patchBrand",
     *      tags={"Brand"},
     *      summary="Partially update specific brand",
     *      description="Partially update specific brand",
     *      @OA\Parameter(
     *          name="brandId",
     *          in="path",
     *          description="The brandId parameter in path",
     *          required=true,
     *          example=1,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Partial brand request object. Only fields to be updated should be included.",
     *          @OA\JsonContent(ref="#/components/schemas/BrandRequest")
     *      ),
     *      @OA\Response(response="200", ref="#/components/responses/UpdateResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     * )
     */
    public function patch(PatchBrand $request, $id)
    {
        $validatedData = $request->validated();
        $updated = $this->brandService->updateBrand($id, $validatedData);
        return $this->preferredFormat(['success' => (bool)$updated], ResponseAlias::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *      path="/brands/{brandId}",
     *      operationId="deleteBrand",
     *      tags={"Brand"},
     *      summary="Delete specific brand",
     *      description="Admin role is required to delete a specific brand",
     *      @OA\Parameter(
     *          name="brandId",
     *          in="path",
     *          description="The brandId parameter in path",
     *          required=true,
     *          example=1,
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
    public function destroy(DestroyBrand $request, $id)
    {
        try {
            $this->brandService->deleteBrand($id);
            return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return $this->preferredFormat([
                    'success' => false,
                    'message' => 'Seems like this brand is used elsewhere.',
                ], ResponseAlias::HTTP_CONFLICT);
            }
        }
    }
}
