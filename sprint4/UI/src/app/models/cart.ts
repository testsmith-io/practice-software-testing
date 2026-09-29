// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

// Sprint 4 keeps the cart in sessionStorage as a flat list of line items.
export interface CartItem {
  id: number;
  name?: string;
  price: number;
  quantity: number;
  total?: number;
  // Header compares `is_rental === 1`, but the product detail stores the raw
  // boolean flag — accept both to match the existing runtime shape.
  is_rental?: number | boolean;
}
