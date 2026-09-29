// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

// sessionStorage-backed cart line item. Extra display fields (name, brand,
// image, ...) vary, so an index signature keeps template access flexible.
export interface CartItem {
  id: number;
  name?: string;
  price: number;
  quantity: number;
  total?: number;
  is_rental?: number | boolean;
  [key: string]: unknown;
}
