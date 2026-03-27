<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Throwable;

class CartService
{
    public function createCart(array $data)
    {
        $cart = new Cart($data);
        $cart->save();

        Log::info('Cart created', ['cart_id' => $cart->id]);

        return $cart->id;
    }

    public function addItemToCart($cartId, $productId, $quantity, $lat = null, $lng = null)
    {
        Log::debug('Adding item to cart', compact('cartId', 'productId', 'quantity', 'lat', 'lng'));

        $cart = Cart::with('cartItems')->find($cartId);
        if (!$cart) {
            Log::warning('Cart not found', ['cart_id' => $cartId]);
            throw new ModelNotFoundException('Cart doesnt exists.');
        }

        $product = Product::findOrFail($productId);

        try {
            if ($product->name === 'Thor Hammer') {
                $existingThorHammer = $cart->cartItems()->where('product_id', $productId)->first();
                if ($existingThorHammer || $quantity > 1) {
                    Log::warning('Thor Hammer constraint violated', ['cart_id' => $cartId]);
                    throw new Exception('You can only have one Thor Hammer in the cart.');
                }

                $cart->cartItems()->create([
                    'product_id' => $productId,
                    'quantity' => 1
                ]);

                Log::info('Thor Hammer added to cart', ['cart_id' => $cartId, 'product_id' => $productId]);
            } else {
                $existingItem = $cart->cartItems()->firstOrCreate(['product_id' => $productId]);
                $existingItem->increment('quantity', $quantity);

                Log::info('Item added/incremented in cart', [
                    'cart_id' => $cartId,
                    'product_id' => $productId,
                    'quantity' => $quantity
                ]);
            }

            if ($cart->lat && $cart->lng && isset($existingItem->product->is_location_offer)) {
                $discount = $this->calculateDiscountPercentage($cart->lat, $cart->lng);
                $existingItem->discount_percentage = $discount;
                $existingItem->save();

                Log::info('Location-based discount applied', [
                    'cart_id' => $cartId,
                    'product_id' => $productId,
                    'discount' => $discount
                ]);
            }

            $this->updateCartDiscounts($cart);
        } catch (Throwable $e) {
            Log::error('Error adding item to cart', [
                'cart_id' => $cartId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getCartById($cartId)
    {
        $cart = Cart::with(['cartItems', 'cartItems.product'])->find($cartId);

        if (!$cart) {
            Log::warning('Cart not found', ['cart_id' => $cartId]);
            throw new ModelNotFoundException('Cart not found.');
        }

        foreach ($cart->cartItems as $cartItem) {
            if ($cartItem->product && $cartItem->discount_percentage) {
                $cartItem->discounted_price = round($cartItem->product->price * (1 - ($cartItem->discount_percentage / 100)), 2);
            }
        }

        Log::debug('Cart retrieved', ['cart_id' => $cartId]);

        return $cart;
    }

    public function updateCartItemQuantity($cartId, $productId, $quantity)
    {
        Log::debug('Updating cart item quantity', compact('cartId', 'productId', 'quantity'));

        $cart = Cart::with('cartItems')->find($cartId);
        if (!$cart) {
            Log::warning('Cart not found for update', ['cart_id' => $cartId]);
            throw new ModelNotFoundException('Cart doesn\'t exist');
        }

        $product = Product::findOrFail($productId);

        if ($product->name === 'Thor Hammer' && $quantity > 1) {
            Log::warning('Attempted to set Thor Hammer quantity > 1', ['cart_id' => $cartId]);
            throw new Exception('You can only have one Thor Hammer in the cart.');
        }

        $updated = $cart->cartItems()
            ->where('product_id', $productId)
            ->update(['quantity' => $quantity]);

        Log::info('Cart item quantity updated', [
            'cart_id' => $cartId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'updated' => (bool)$updated
        ]);

        return (bool)$updated;
    }

    public function deleteCart($cartId)
    {
        $cart = Cart::with('cartItems')->find($cartId);
        if (!$cart) {
            Log::warning('Attempted to delete non-existent cart', ['cart_id' => $cartId]);
            throw new ModelNotFoundException('Cart doesnt exists.');
        }

        $cart->cartItems()->delete();
        $cart->delete();

        Log::info('Cart deleted', ['cart_id' => $cartId]);
    }

    public function removeProductFromCart($cartId, $productId)
    {
        $cart = Cart::with('cartItems')->find($cartId);
        if (!$cart) {
            Log::warning('Cart not found when removing product', ['cart_id' => $cartId]);
            throw new ModelNotFoundException('Cart doesnt exists.');
        }

        $cart->cartItems()->where('product_id', $productId)->delete();
        $this->updateCartDiscounts($cart);

        Log::info('Product removed from cart', [
            'cart_id' => $cartId,
            'product_id' => $productId
        ]);
    }

    private function updateCartDiscounts($cart)
    {
        $cart->load('cartItems.product');

        $hasProduct = $cart->cartItems->contains(fn($item) => !$item->product->is_rental);
        $hasRental = $cart->cartItems->contains(fn($item) => $item->product->is_rental);

        $cart->additional_discount_percentage = $hasProduct && $hasRental ? 15 : null;
        $cart->save();

        Log::debug('Cart discounts updated', [
            'cart_id' => $cart->id,
            'discount_applied' => $cart->additional_discount_percentage
        ]);
    }

    private function calculateDiscountPercentage($lat, $lng)
    {
        Log::info('Coordinates', ['lat' => $lat, 'lng' => $lng]);
        $coordinates = [
            "new york" => ["lat" => 41, "lng" => 74, "discount_percentage" => 5],
            "mumbai" => ["lat" => 19, "lng" => 73, "discount_percentage" => 10],
            "tokyo" => ["lat" => 35, "lng" => 139, "discount_percentage" => 15],
            "amsterdam" => ["lat" => 52, "lng" => 5, "discount_percentage" => 20],
            "london" => ["lat" => 51, "lng" => 0, "discount_percentage" => 25],
        ];

        foreach ($coordinates as $city => $data) {
            if (abs($lat - $data["lat"]) <= 2 && abs($lng - $data["lng"]) <= 2) {
                Log::info('Location matched for discount', ['city' => $city, 'discount' => $data['discount_percentage']]);
                return $data["discount_percentage"];
            }
        }

        return 0;
    }
}
