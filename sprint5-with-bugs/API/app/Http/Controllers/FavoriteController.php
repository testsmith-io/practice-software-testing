<?php

namespace App\Http\Controllers;

use App\Http\Requests\Favorite\DestroyFavorite;
use App\Http\Requests\Favorite\StoreFavorite;
use App\Models\Favorite;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FavoriteController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:users');
    }

    /**
     * @OA\Get(
     *      path="/favorites",
     *      operationId="getFavorites",
     *      tags={"Favorite"},
     *      summary="Retrieve all favorites",
     *      description="User role is required to retrieve users favorites",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/FavoriteResponse")
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Returns when user is not authenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Returns when the requested item is not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Requested item not found"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Returns when the method is not allowed for the requested route",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route"),
     *          )
     *      ),
     *     security={{ "apiAuth": {} }}
     * )
     */
    public function index()
    {
        return $this->preferredFormat(Favorite::with('product', 'product.product_image')->where('user_id', app('auth')->user()->id)->get());
    }

    /**
     * @OA\Post(
     *      path="/favorites",
     *      operationId="storeFavorite",
     *      tags={"Favorite"},
     *      summary="Store new favorite",
     *      description="User role is required to store new favorite",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Brand request object",
     *          @OA\JsonContent(ref="#/components/schemas/FavoriteRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/FavoriteResponse")
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Returns when user is not authenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Returns when the requested item is not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Requested item not found"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Returns when the method is not allowed for the requested route",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Returns when the server was not able to process the content",
     *      ),
     *     security={{ "apiAuth": {} }}
     * )
     */
    public function store(StoreFavorite $request)
    {
        $input = $request->all();
        $input['user_id'] = app('auth')->user()->id;

        return $this->preferredFormat(Favorite::create($input), ResponseAlias::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *      path="/favorites/{favoriteId}",
     *      operationId="getFavorite",
     *      tags={"Favorite"},
     *      summary="Retrieve specific favorite",
     *      description="User role is required to retrieve specific favorite",
     *      @OA\Parameter(
     *          name="favoriteId",
     *          in="path",
     *          example=1,
     *          description="The favoriteId parameter in path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/FavoriteResponse")
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Returns when user is not authenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Returns when the requested item is not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Requested item not found"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Returns when the method is not allowed for the requested route",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route"),
     *          )
     *      ),
     *     security={{ "apiAuth": {} }}
     * )
     */
    public function show($id)
    {
        return $this->preferredFormat(Favorite::findOrFail($id));
    }

    /**
     * @OA\Put(
     *      path="/favorites/{favoriteId}",
     *      operationId="updateFavorite",
     *      tags={"Favorite"},
     *      summary="Update specific favorite",
     *      description="User role is required to update specific favorite",
     *      @OA\Parameter(
     *          name="favoriteId",
     *          in="path",
     *          description="The favoriteId parameter in path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Brand request object",
     *          @OA\JsonContent(ref="#/components/schemas/FavoriteRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="A token",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="success",
     *                       type="boolean",
     *                       example=true,
     *                       description=""
     *                  ),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Returns when user is not authenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Returns when the resource is not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Resource not found"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Returns when the method is not allowed for the requested route",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Returns when the server was not able to process the content",
     *      ),
     *     security={{ "apiAuth": {} }}
     *
     * )
     */
    public function update(StoreFavorite $request, $id)
    {
        return $this->preferredFormat(['success' => (bool)Favorite::where('id', $id)->update($request->all())], ResponseAlias::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *      path="/favorites/{favoriteId}",
     *      operationId="deleteFavorite",
     *      tags={"Favorite"},
     *      summary="Delete specific favorite",
     *      description="User role is required to delete a specific favorite",
     *      @OA\Parameter(
     *          name="favoriteId",
     *          in="path",
     *          description="The favoriteId parameter in path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="Successful operation"
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Returns when user is not authenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Returns when the resource is not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Resource not found"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=409,
     *          description="Returns when the entity is used elsewhere",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Returns when the method is not allowed for the requested route",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Returns when the server was not able to process the content",
     *      ),
     *     security={{ "apiAuth": {} }}
     * ),
     */
    public function destroy(DestroyFavorite $request, $id)
    {
        try {
            Favorite::where('user_id', app('auth')->user()->id)->where('product_id', $id)->delete();
            return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return $this->preferredFormat([
                    'success' => false,
                    'message' => 'Seems like this Brand is used elsewhere.',
                ], ResponseAlias::HTTP_CONFLICT);
            }
        }
    }
}
