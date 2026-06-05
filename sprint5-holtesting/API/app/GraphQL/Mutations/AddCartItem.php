<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\GraphQL\Mutations;

use App\Models\CartItem;
use App\Services\CartService;

class AddCartItem
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function __invoke($_, array $args): CartItem
    {
        $this->cartService->addItemToCart(
            $args['cart_id'],
            $args['product_id'],
            $args['quantity'] ?? 1
        );

        return CartItem::where('cart_id', $args['cart_id'])
            ->where('product_id', $args['product_id'])
            ->firstOrFail();
    }
}
