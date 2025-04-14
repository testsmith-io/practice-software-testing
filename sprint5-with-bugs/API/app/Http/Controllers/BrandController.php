<?php

namespace App\Http\Controllers;

use App\Http\Requests\Brand\DestroyBrand;
use App\Http\Requests\Brand\StoreBrand;
use App\Http\Requests\Brand\UpdateBrand;
use App\Models\Brand;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/BrandResponse")
     *          )
     *      ),
     *      @OA\Response(response="404", ref="#/components/responses/ResourceNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *  )
     */
    public function index()
    {
        Log::info('Fetching all brands');
        $brands = Brand::all();
        Log::debug('Brands retrieved', ['count' => $brands->count()]);
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
     *      ),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     *  )
     */
    public function store(StoreBrand $request)
    {
        Log::info('Creating a new brand', ['payload' => $request->all()]);
        $brand = Brand::create($request->all());
        Log::debug('Brand created', ['brand' => $brand]);
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
        Log::info("Fetching brand by ID", ['id' => $id]);
        $brand = Brand::findOrFail($id);
        Log::debug('Brand found', ['brand' => $brand]);
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
     *  )
     */
    public function search(Request $request)
    {
        $q = $request->get('q');
        Log::info('Searching brands', ['query' => $q]);
        $results = Brand::where('name', 'like', "%$q%")->get();
        Log::debug('Search results', ['count' => $results->count()]);
        return $this->preferredFormat($results);
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
     *          @OA\Schema(type="integer")
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
     *  )
     */
    public function update(UpdateBrand $request, $id)
    {
        Log::info('Updating brand', ['id' => $id, 'payload' => $request->all()]);
        $updated = Brand::where('id', $id)->update($request->all());
        Log::debug('Update result', ['updated' => $updated]);
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
    public function destroy(DestroyBrand $request, $id)
    {
        Log::info('Deleting brand', ['id' => $id]);

        try {
            $brand = Brand::find($id);

            if (!$brand) {
                Log::warning('Attempted to delete non-existent brand', ['id' => $id]);
                abort(ResponseAlias::HTTP_NOT_FOUND, 'Brand not found');
            }

            $brand->delete();
            Log::debug('Brand deleted successfully', ['id' => $id]);
            return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);

        } catch (QueryException $e) {
            Log::error('QueryException during brand deletion', [
                'id' => $id,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            if ($e->getCode() === '23000') {
                return $this->preferredFormat([
                    'success' => false,
                    'message' => 'Seems like this brand is used elsewhere.',
                ], ResponseAlias::HTTP_CONFLICT);
            }

            throw $e;
        }
    }
}
