<?php

namespace App\Http\Controllers;

use App\Http\Requests\Favorite\DestroyFavorite;
use App\Http\Requests\Favorite\StoreFavorite;
use App\Services\FavoriteService;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FavoriteController extends Controller
{

    private $favoriteService;

    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
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
     *              @OA\Items(ref="#/components/schemas/FavoriteWithProductResponse")
     *          )
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function index()
    {
        $favorites = $this->favoriteService->getAllFavorites();
        return $this->preferredFormat($favorites);
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
    public function store(StoreFavorite $request)
    {
        $favorite = $this->favoriteService->createFavorite($request->all());
        return $this->preferredFormat($favorite, ResponseAlias::HTTP_CREATED);
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
    public function show($id)
    {
        $favorite = $this->favoriteService->getFavoriteById($id);
        return $this->preferredFormat($favorite);
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
    public function destroy(DestroyFavorite $request, $id)
    {
        $this->favoriteService->deleteFavorite($id);
        return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
    }
}
