// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Product} from "./product";

export interface Invoiceline {
  id?: number;
  product?: Product;
  quantity?: number;
  unit_price?: number;
}
