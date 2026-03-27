<?php

namespace App\Http\Controllers;

use App\Http\Requests\Favorite\DestroyFavorite;
use App\Http\Requests\Favorite\StoreFavorite;
use App\Models\Favorite;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
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
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function index()
    {
        $user = app('auth')->user();
        Log::info('Fetching favorites', ['user_id' => $user->id]);

        $favorites = Favorite::with('product', 'product.product_image')
            ->where('user_id', $user->id)
            ->get();

        Log::debug('Favorites retrieved', ['count' => $favorites->count()]);
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
        $user = app('auth')->user();
        $input = $request->all();
        $input['user_id'] = $user->id;

        Log::info('Creating favorite', ['user_id' => $user->id, 'product_id' => $input['product_id'] ?? null]);

        $favorite = Favorite::create($input);

        Log::debug('Favorite created', ['favorite_id' => $favorite->id]);

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
     *          @OA\Schema(type="integer")
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
        Log::info('Fetching favorite', ['favorite_id' => $id]);
        $favorite = Favorite::findOrFail($id);
        Log::debug('Favorite found', ['favorite_id' => $favorite->id]);

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
    public function destroy(DestroyFavorite $request, $id)
    {
        $user = app('auth')->user();

        try {
            Log::info('Attempting to delete favorite', ['user_id' => $user->id, 'product_id' => $id]);

            $deleted = Favorite::where('user_id', $user->id)->where('product_id', $id)->delete();

            Log::debug('Favorite deletion result', ['deleted' => $deleted]);

            return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
        } catch (QueryException $e) {
            Log::error('Error deleting favorite', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'user_id' => $user->id,
                'product_id' => $id
            ]);

            if ($e->getCode() === '23000') {
                return $this->preferredFormat([
                    'success' => false,
                    'message' => 'Seems like this Brand is used elsewhere.',
                ], ResponseAlias::HTTP_CONFLICT);
            }

            throw $e; // Re-throw if not handled
        }
    }
}
