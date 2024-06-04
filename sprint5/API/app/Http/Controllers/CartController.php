<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CartController extends Controller
{

    public function __construct()
    {
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
        $cart = new Cart($request->only(['lat', 'lng']));
        $cart->save();

        return $this->preferredFormat(['id' => $cart->id], ResponseAlias::HTTP_CREATED);
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
        $cart = Cart::with('cartItems')->findOrFail($id);

        $product_id = $request->input('product_id');
        $quantity = $request->input('quantity');

        $existingItem = $cart->cartItems()->firstOrCreate(['product_id' => $product_id]);
        $existingItem->increment('quantity', $quantity);

        if ($cart->lat && $cart->lng && $existingItem->product->is_location_offer) {
            $existingItem->discount_percentage = $this->calculateDiscountPercentage($cart->lat, $cart->lng);
            $existingItem->save();
        }

        $this->updateCartDiscounts($cart);

        return $this->preferredFormat(['result' => 'item added or updated'], ResponseAlias::HTTP_OK);
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
        $cart = Cart::with(['cartItems', 'cartItems.product'])->findOrFail($id);

        foreach ($cart->cartItems as $cartItem) {
            if ($cartItem->product && $cartItem->discount_percentage) {
                $cartItem->discounted_price = round($cartItem->product->price * (1 - ($cartItem->discount_percentage / 100)), 2);
            }
        }

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
        $cart = Cart::with('cartItems')->find($cartId);

        if (!isset($cart)) {
            return $this->preferredFormat(['message' => 'Cart doesnt exists'], ResponseAlias::HTTP_NOT_FOUND);
        }
        $updateStatus = $cart->cartItems()
            ->where('product_id', $request->input('product_id'))
            ->update(['quantity' => $request->input('quantity')]);

        return $this->preferredFormat(['success' => (bool)$updateStatus], ResponseAlias::HTTP_OK);
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
        $cart = Cart::with('cartItems')->find($cartId);

        if (!isset($cart)) {
            return $this->preferredFormat(['message' => 'Cart doesnt exists'], ResponseAlias::HTTP_NOT_FOUND);
        }

        $cart->cartItems()->delete();
        $cart->delete();
        return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
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
        // Find the cart
        $cart = Cart::with('cartItems')->find($cartId);

        if (!isset($cart)) {
            return $this->preferredFormat(['message' => 'Cart doesnt exists'], ResponseAlias::HTTP_NOT_FOUND);
        }

        $cart->cartItems()->where('product_id', $productId)->delete();
        $this->updateCartDiscounts($cart);
        return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
    }

    private function updateCartDiscounts($cart)
    {
        $cart->load('cartItems.product');
        $hasProduct = $cart->cartItems->contains(fn($item) => !$item->product->is_rental);
        $hasRental = $cart->cartItems->contains(fn($item) => $item->product->is_rental);

        $cart->additional_discount_percentage = $hasProduct && $hasRental ? 15 : null;
        $cart->save();
    }

    private function calculateDiscountPercentage($lat, $lng)
    {
        $coordinates = [
            "new york" => ["lat" => 41, "lng" => 74, "discount_percentage" => 5],
            "mumbai" => ["lat" => 19, "lng" => 73, "discount_percentage" => 10],
            "tokyo" => ["lat" => 35, "lng" => 139, "discount_percentage" => 15],
            "amsterdam" => ["lat" => 52, "lng" => 5, "discount_percentage" => 20],
            "london" => ["lat" => 51, "lng" => 0, "discount_percentage" => 25],
        ];

        // Initialize a default discount percentage
        $defaultDiscountPercentage = 0;

        // Check if the provided coordinates match any of the predefined coordinates
        foreach ($coordinates as $data) {
            $cityLat = $data["lat"];
            $cityLng = $data["lng"];
            $discount = $data["discount_percentage"];

            // Check if the provided lat and lng values are within +/- 2 of the predefined values
            if (abs($lat - $cityLat) <= 2 && abs($lng - $cityLng) <= 2) {
                return $discount;
            }
        }

        return $defaultDiscountPercentage;
    }
}
