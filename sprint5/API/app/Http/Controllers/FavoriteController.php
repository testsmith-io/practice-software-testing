<?php

namespace App\Http\Controllers;

use App\Http\Requests\Favorite\DestroyFavorite;
use App\Http\Requests\Favorite\StoreFavorite;
use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FavoriteController extends Controller {

    public function __construct() {
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
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function index() {
        return $this->preferredFormat(Favorite::with('product', 'product.product_image')->where('user_id', Auth::user()->id)->get());
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
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function store(StoreFavorite $request) {
        $input = $request->all();
        $input['user_id'] = Auth::user()->id;

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
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/FavoriteResponse")
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function show($id) {
        return $this->preferredFormat(Favorite::findOrFail($id));
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
    public function destroy(DestroyFavorite $request, $id) {
        Favorite::where('user_id', Auth::user()->id)->where('product_id', $id)->delete();
        return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
    }
}
