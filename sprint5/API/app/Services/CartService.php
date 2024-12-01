<?php


namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartService
{
    public function createCart(array $data)
    {
        $cart = new Cart($data);
        $cart->save();

        return $cart->id;
    }

    public function addItemToCart($cartId, $productId, $quantity, $lat = null, $lng = null)
    {
        $cart = Cart::with('cartItems')->find($cartId);

        if (!$cart) {
            throw new ModelNotFoundException('Cart doesnt exists.');
        }
        $product = Product::findOrFail($productId);

        if ($product->name === 'Thor Hammer') {
            $existingThorHammer = $cart->cartItems()->where('product_id', $productId)->first();
            if ($existingThorHammer || $quantity > 1) {
                throw new \Exception('You can only have one Thor Hammer in the cart.');
            }

            $cart->cartItems()->create([
                'product_id' => $productId,
                'quantity' => 1
            ]);
        } else {
            $existingItem = $cart->cartItems()->firstOrCreate(['product_id' => $productId]);
            $existingItem->increment('quantity', $quantity);
        }

        if ($lat && $lng && isset($existingItem->product->is_location_offer)) {
            $existingItem->discount_percentage = $this->calculateDiscountPercentage($lat, $lng);
            $existingItem->save();
        }

        $this->updateCartDiscounts($cart);
    }

    public function getCartById($cartId)
    {
        $cart = Cart::with(['cartItems', 'cartItems.product'])->find($cartId);

        if (!$cart) {
            throw new ModelNotFoundException('Cart not found.');
        }

        foreach ($cart->cartItems as $cartItem) {
            if ($cartItem->product && $cartItem->discount_percentage) {
                $cartItem->discounted_price = round($cartItem->product->price * (1 - ($cartItem->discount_percentage / 100)), 2);
            }
        }

        return $cart;
    }

    public function updateCartItemQuantity($cartId, $productId, $quantity)
    {
        $cart = Cart::with('cartItems')->find($cartId);

        if (!$cart) {
            throw new ModelNotFoundException('Cart doesn\'t exist');
        }

        $product = Product::findOrFail($productId);

        if ($product->name === 'Thor Hammer' && $quantity > 1) {
            throw new \Exception('You can only have one Thor Hammer in the cart.');
        }

        $updateStatus = $cart->cartItems()
            ->where('product_id', $productId)
            ->update(['quantity' => $quantity]);

        return (bool) $updateStatus;
    }

    public function deleteCart($cartId)
    {
        $cart = Cart::with('cartItems')->find($cartId);
        if (!$cart) {
            throw new ModelNotFoundException('Cart doesnt exists.');
        }
        $cart->cartItems()->delete();
        $cart->delete();
    }

    public function removeProductFromCart($cartId, $productId)
    {
        $cart = Cart::with('cartItems')->find($cartId);
        if (!$cart) {
            throw new ModelNotFoundException('Cart doesnt exists.');
        }
        $cart->cartItems()->where('product_id', $productId)->delete();
        $this->updateCartDiscounts($cart);
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

        foreach ($coordinates as $data) {
            if (abs($lat - $data["lat"]) <= 0.5 && abs($lng - $data["lng"]) <= 0.5) {
                return $data["discount_percentage"];
            }
        }

        return 0;
    }
}
