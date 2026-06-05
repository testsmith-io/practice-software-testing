// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Product} from "./product";

export interface Invoiceline {
  id?: string;
  invoice_id: string;
  product_id: string;
  product?: Product;
  quantity?: number;
  unit_price?: number;
  discount_percentage: number;
  discounted_price: number;
}
