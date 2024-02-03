<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CartController extends Controller {

    public function __construct() {
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
     *                @OA\Property(property="cart_id",
     *                         type="string",
     *                         example="1234",
     *                         description=""
     *                     )
     *              )
     *          )
     *      ),
     * @OA\Response(
     *          response=404,
     *          description="Returns when requested item is not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Requested item not found"),
     *          )
     *      ),
     * @OA\Response(
     *          response=405,
     *          description="Returns when the method is not allowed for the requested route",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route"),
     *          )
     *      ),
     * @OA\Response(
     *          response=422,
     *          description="Returns when the server was not able to process the content",
     *      ),
     * )
     */
    public function createCart(Request $request) {
        $lat = $request->input('lat');
        $lng = $request->input('lng');

        $cart = new Cart();
        if (isset($lat) && isset($lng)) {
            $cart->lat = $lat;
            $cart->lng = $lng;
        }
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
     *                @OA\Property(property="result",
     *                         type="string",
     *                         example="item added or updated",
     *                         description=""
     *                     )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Returns when requested item is not found",
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
     * )
     */
    public function addItem(Request $request, $id) {
        // Find the cart
        $cart = Cart::with('cartItems')->find($id);

        if (!isset($cart)) {
            return $this->preferredFormat(['message' => 'Cart doesnt exists'], ResponseAlias::HTTP_CREATED);
        }

        $product_id = $request->input('product_id');
        $quantity = $request->input('quantity');

        // Check if the item with the same product_id already exists in the cart
        $existingItem = $cart->cartItems->where('product_id', $product_id)->first();

        if ($existingItem) {
            // If the item exists, update its quantity
            $existingItem->quantity += $quantity;
            $existingItem->save();
        } else {
            // If the item does not exist, create a new CartItem and add it to the cart
            $item = new CartItem();
            // Check if lat and lng are set
            if ($cart->lat !== null && $cart->lng !== null) {
                // Calculate the discount percentage based on lat and lng
                $product = Product::find($product_id);
                if($product->is_location_offer) {
                    $lat = $cart->lat;
                    $lng = $cart->lng;
                    $discountPercentage = $this->calculateDiscountPercentage($lat, $lng);

                    // Store the discount percentage in the database
                    $item->discount_percentage = $discountPercentage;

                }
            }

            $item->product_id = $product_id;
            $item->quantity = $quantity;

            $cart->cartItems()->save($item);
        }

        $this->updateCartDiscounts($cart);

        // Return a response indicating success or any additional data you need
        return $this->preferredFormat(['result' => 'item added or updated'], ResponseAlias::HTTP_CREATED);
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
     *       ),
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
     * )
     */
    public function getCart($id) {
        // Find the cart
        $cart = Cart::with('cartItems', 'cartItems.product')->findOrFail($id);
        // Iterate through cart items to calculate discounted prices
        foreach ($cart->cartItems as $cartItem) {
            if ($cartItem->product && $cartItem->discount_percentage !== null) {
                // Calculate discounted price
                $discountedPrice = $cartItem->product->price * (1 - ($cartItem->discount_percentage / 100));
                // Round the discounted price to two decimal places
                $discountedPrice = round($discountedPrice, 2);
                // Add the discounted price to the cartItem object
                $cartItem->discounted_price = $discountedPrice;
            }
        }

        // Return the cart with discounted prices
        return $this->preferredFormat($cart);
    }

    /**
     * @OA\Put(
     *      path="/carts/{cartId}",
     *      operationId="updateCartQuantity",
     *      tags={"Cart"},
     *      summary="Update quantity of item in cart",
     *      description="Update quantity of item in cart",
    *       @OA\Parameter(
     *            name="cartId",
     *            in="path",
     *            required=true,
     *            @OA\Schema(type="string"),
     *            description="Cart ID"
     *        ),
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
     *        ),
     *      @OA\Response(
     *           response=200,
     *           description="Item added",
     *          @OA\MediaType(
     *                  mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="success",
     *                          type="boolean",
     *                          example="true",
     *                          description=""
     *                      )
     *               )
     *           )
     *       ),
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
     * )
     */
    public function updateQuantity(Request $request, $cartId) {
        // Find the cart
        $cart = Cart::with('cartItems')->find($cartId);

        if (!isset($cart)) {
            return $this->preferredFormat(['message' => 'Cart doesnt exists'], ResponseAlias::HTTP_CREATED);
        }

        return $this->preferredFormat(['success' => (bool)CartItem::where('cart_id', '=', $cart->id)->where('product_id', '=', $request->input('product_id'))->update(['quantity' => $request->input('quantity')])], ResponseAlias::HTTP_OK);
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
     *    @OA\Parameter(
     *           name="productId",
     *           in="path",
     *           description="The cartId parameter in path",
     *           required=true,
     *           example=1,
     *           @OA\Schema(type="string")
     *       ),
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
     *      )
     * ),
     */
    public function deleteCart($cartId) {
        // Find the cart
        $cart = Cart::with('cartItems')->find($cartId);

        if (!isset($cart)) {
            return $this->preferredFormat(['message' => 'Cart doesnt exists'], ResponseAlias::HTTP_CREATED);
        }

        try {
            CartItem::where('cart_id', '=', $cartId)->delete();
            Cart::destroy($cartId);
            return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return $this->preferredFormat([
                    'success' => false,
                    'message' => 'Seems like this cart is used elsewhere.',
                ], ResponseAlias::HTTP_CONFLICT);
            }
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
     *    @OA\Parameter(
     *           name="productId",
     *           in="path",
     *           description="The cartId parameter in path",
     *           required=true,
     *           example=1,
     *           @OA\Schema(type="string")
     *       ),
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
     *      )
     * ),
     */
    public function removeProductFromCart($cartId, $productId) {
        // Find the cart
        $cart = Cart::with('cartItems')->find($cartId);

        if (!isset($cart)) {
            return $this->preferredFormat(['message' => 'Cart doesnt exists'], ResponseAlias::HTTP_CREATED);
        }

        try {
            CartItem::where('cart_id', '=', $cart->id)->where('product_id', '=', $productId)->delete();
            $this->updateCartDiscounts($cart);
            return $this->preferredFormat(null, ResponseAlias::HTTP_NO_CONTENT);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return $this->preferredFormat([
                    'success' => false,
                    'message' => 'Seems like this cart is used elsewhere.',
                ], ResponseAlias::HTTP_CONFLICT);
            }
        }
    }

    private function updateCartDiscounts($cart) {
        $cart->load('cartItems.product');
        $hasProduct = $cart->cartItems->contains(fn($item) => !$item->product->is_rental);
        $hasRental = $cart->cartItems->contains(fn($item) => $item->product->is_rental);

        if ($hasProduct && $hasRental) {
            $cart->additional_discount_percentage = 15;
            $cart->save();
        } else {
            $cart->additional_discount_percentage = null;
            $cart->save();
        }
    }

    private function calculateDiscountPercentage($lat, $lng) {
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
        foreach ($coordinates as $city => $data) {
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
