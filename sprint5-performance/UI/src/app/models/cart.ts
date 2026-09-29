// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Product} from "./product";

export interface CartItem {
  product: Product;
  quantity: number;
  discount_percentage?: number;
  discounted_price?: number;
}

export interface Cart {
  id?: string;
  cart_items: CartItem[];
  additional_discount_percentage?: number;
}
