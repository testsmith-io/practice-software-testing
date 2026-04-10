// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Image} from "./image";
import {Brand} from "./brand";
import {Category} from "./category";

export interface ProductSpec {
  id?: string;
  product_id?: string;
  spec_name: string;
  spec_value: string;
  spec_unit?: string;
}

export interface Product {
  id?: string;
  name?: string;
  description?: string;
  price?: number;
  in_stock?: boolean;
  is_location_offer?: boolean;
  is_rental?: boolean;
  discount_price?: number;
  product_image_id?: number;
  product_image?: Image;
  brand_id?: number;
  category_id?: number;
  brand?: Brand;
  category?: Category;
  co2_rating?: string;
  is_eco_friendly?: boolean;
  specs?: ProductSpec[];
}
