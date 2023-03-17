import {Component, OnInit} from '@angular/core';
import {CartService} from "../../_services/cart.service";
import {ActivatedRoute} from "@angular/router";
import {ToastService} from "../../_services/toast.service";
import {Product} from "../../models/product";
import {ProductService} from "../../_services/product.service";
import {Options} from "@angular-slider/ngx-slider";
import {BrowserDetectorService} from "../../_services/browser-detector.service";

@Component({
  selector: 'app-detail',
  templateUrl: './detail.component.html',
  styleUrls: ['./detail.component.css']
})
export class DetailComponent implements OnInit {
  product: Product;
  quantity: number = 1;
  relatedProducts: Product[];
  private sub: any;
  private id: number;
  sliderOptions: Options = {
    floor: 1,
    ceil: 10
  };

  constructor(private cartService: CartService,
              private route: ActivatedRoute,
              private toastService: ToastService,
              private productService: ProductService,
              public browserDetect: BrowserDetectorService) {
  }

  ngOnInit(): void {
    this.sub = this.route.params.subscribe(params => {
      this.id = +params['id'];
      this.getProduct(this.id);
      this.getRelatedProducts(this.id);
    });
  }


  plus() {
    this.quantity = this.quantity + 1;
  }

  minus() {
    if (this.quantity != 1) {
      this.quantity = this.quantity - 1;
    }
  }

  getProduct(id: number) {
    this.productService.getProduct(id).subscribe(response => {
      this.product = response;
    });
  }

  getRelatedProducts(id: number) {
    this.productService.getRelatedProducts(id).subscribe(response => {
      this.relatedProducts = response;
    });
  }

  addToCart(product: Product) {
    if (this.quantity >= 1) {
      const price = (product.discount_price) ? product.discount_price : product.price;
      let item = {
        'id': product.id,
        'is_rental': product.is_rental,
        'name': product.name,
        'quantity': this.quantity,
        'price': price,
        'total': this.quantity * price
      }
      this.cartService.addItem(item);
      this.toastService.show('Product added to shopping cart.', {classname: 'bg-success text-light'})
    }
  }

}
