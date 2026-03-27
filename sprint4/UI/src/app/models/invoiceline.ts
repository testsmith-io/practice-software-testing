import {Product} from "./product";

export interface Invoiceline {
  id?: number;
  product?: Product;
  quantity?: number;
  unit_price?: number;
}
