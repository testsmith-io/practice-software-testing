import {Product} from "./product";

export class Invoiceline {
  id!: number;
  product!: Product;
  quantity!: number;
  unit_price!: number;
}
