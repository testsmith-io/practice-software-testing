<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CartController extends Controller
{

    private $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
        $this->middleware('role:admin', ['only' => ['destroy']]);
    }

    /**
     * @OA\Post(
     *      path="/carts",
     *      operationId="createCart",
     *      tags={"Cart"},
     *      summary="Create a new cart",
     *      description="Create a new cart",
     *    @OA\Response(
     *          response=201,
     *          description="Create cartId",
     *         @OA\MediaType(
     *                 mediaType="application/json",
     *            @OA\Schema(
     *                title="CartCreatedResponse",
     *                @OA\Property(property="cart_id",
     *                         type="string",
     *                         example="1234",
     *                         description=""
     *                     )
     *              )
     *          )
     *      ),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     * )
     */
    public function createCart(Request $request)
    {
        $cartId = $this->cartService->createCart($request->only(['lat', 'lng']));
        return $this->preferredFormat(['id' => $cartId], ResponseAlias::HTTP_CREATED);
    }

    /**
     * @OA\Post(
     *      path="/carts/{id}",
     *      operationId="addItem",
     *      tags={"Cart"},
     *      summary="Add item to cart",
     *      description="Add item to cart",
     *      @OA\Parameter(
     *           name="id",
     *           in="path",
     *           required=true,
     *           @OA\Schema(type="string"),
     *           description="Cart ID"
     *       ),
     *       @OA\RequestBody(
     *           required=true,
     *           description="Payload to add item to cart",
     *           @OA\JsonContent(
     *               required={"product_id", "quantity"},
     *               @OA\Property(
     *                   property="product_id",
     *                   type="string",
     *                   example="01HHJC7RERZ0M3VDGS6X9HM33A"
     *               ),
     *               @OA\Property(
     *                   property="quantity",
     *                   type="integer",
     *                   example=1
     *               )
     *           )
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Item added",
     *         @OA\MediaType(
     *                 mediaType="application/json",
     *            @OA\Schema(
     *                title="CartItemAddedResponse",
     *                @OA\Property(property="result",
     *                         type="string",
     *                         example="item added or updated",
     *                         description=""
     *                     )
     *              )
     *          )
     *      ),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     * )
     */
    public function addItem(Request $request, $id)
    {
        try {
            $this->cartService->addItemToCart(
                $id,
                $request->input('product_id'),
                $request->input('quantity'),
                $request->input('lat'),
                $request->input('lng')
            );

            return $this->preferredFormat(['result' => 'item added or updated'], ResponseAlias::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return $this->preferredFormat(['message' => 'Cart not found'], ResponseAlias::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->preferredFormat(['message' => $e->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Get(
     *      path="/carts/{cartId}",
     *      operationId="getCart",
     *      tags={"Cart"},
     *      summary="Retrieve specific cart",
     *      description="Retrieve specific cart",
     *      @OA\Parameter(
     *          name="cartId",
     *          in="path",
     *          example=1,
     *          description="The cartId parameter in path",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/CartResponse")
     *      ),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     * )
     */
    public function getCart($id)
    {
        $cart = $this->cartService->getCartById($id);
        return $this->preferredFormat($cart);
    }

    /**
     * @OA\Put(
     *      path="/carts/{cartId}/product/quantity",
     *      operationId="updateCartQuantity",
     *      tags={"Cart"},
     *      summary="Update quantity of item in cart",
     *      description="Update quantity of item in cart",
     *      @OA\Parameter(
     *           name="cartId",
     *           in="path",
     *           required=true,
     *           @OA\Schema(type="string"),
     *           description="Cart ID"
     *      ),
     *      @OA\RequestBody(
     *            required=true,
     *            description="Payload to add item to cart",
     *            @OA\JsonContent(
     *                required={"product_id", "quantity"},
     *                @OA\Property(
     *                    property="product_id",
     *                    type="string",
     *                    example="01HHJC7RERZ0M3VDGS6X9HM33A"
     *                ),
     *                @OA\Property(
     *                    property="quantity",
     *                    type="integer",
     *                    example=1
     *                )
     *            )
     *       ),
     *       @OA\Response(response="200", ref="#/components/responses/UpdateResponse"),
     *       @OA\Response(response="404", ref="#/components/responses/ResourceNotFoundResponse"),
     *       @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *       @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     * )
     */
    public function updateQuantity(Request $request, $cartId)
    {
        try {
            $success = $this->cartService->updateCartItemQuantity(
                $cartId,
                $request->input('product_id'),
                $request->input('quantity')
            );

            return $this->preferredFormat(['result' => 'item added or updated'], ResponseAlias::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return $this->preferredFormat(['message' => 'Cart doesn\'t exist'], ResponseAlias::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->preferredFormat(['message' => $e->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Delete(
     *      path="/carts/{cartId}",
     *      operationId="deleteCart",
     *      tags={"Cart"},
     *      summary="Delete Cart",
     *      description="Delete Cart",
     *      @OA\Parameter(
     *          name="cartId",
     *          in="path",
     *          description="The cartId parameter in path",
     *          required=true,
     *          example=1,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(response=204, description="Successful operation"),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ResourceNotFoundResponse"),
     *      @OA\Response(response="409", ref="#/components/responses/ConflictResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     * ),
     */
    public function deleteCart($cartId)
    {
        try {
            $this->cartService->deleteCart($cartId);
            return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->preferredFormat(['message' => 'Cart doesnt exists'], ResponseAlias::HTTP_NOT_FOUND);
        }
    }

    /**
     * @OA\Delete(
     *      path="/carts/{cartId}/product/{productId}",
     *      operationId="deleteProductFromCart",
     *      tags={"Cart"},
     *      summary="Delete product from cart",
     *      description="Delete a product from Cart",
     *      @OA\Parameter(
     *          name="cartId",
     *          in="path",
     *          description="The cartId parameter in path",
     *          required=true,
     *          example=1,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *           name="productId",
     *           in="path",
     *           description="The cartId parameter in path",
     *           required=true,
     *           example=1,
     *           @OA\Schema(type="string")
     *      ),
     *      @OA\Response(response=204, description="Successful operation"),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ResourceNotFoundResponse"),
     *      @OA\Response(response="409", ref="#/components/responses/ConflictResponse"),
     *      @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     *      @OA\Response(response="422", ref="#/components/responses/UnprocessableEntityResponse"),
     * ),
     */
    public function removeProductFromCart($cartId, $productId)
    {
        try {
            $this->cartService->removeProductFromCart($cartId, $productId);
            return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->preferredFormat(['message' => 'Cart doesnt exists'], ResponseAlias::HTTP_NOT_FOUND);
        }
    }

}
