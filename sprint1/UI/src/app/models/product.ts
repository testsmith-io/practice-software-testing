import {Image} from "./image";
import {Brand} from "./brand";
import {Category} from "./category";

export class Product {
  id!: number;
  name!: string;
  description!: string;
  price!: number;
  stock!: number;
  discount_price!: number;
  product_image_id!: number;
  product_image!: Image;
  brand_id!: number;
  category_id!: number;
  brand: Brand;
  category: Category;
}
