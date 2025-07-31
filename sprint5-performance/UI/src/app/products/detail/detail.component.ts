import {Component, inject, OnInit} from '@angular/core';
import {CartService} from "../../_services/cart.service";
import {FavoriteService} from "../../_services/favorite.service";
import {ActivatedRoute, RouterLink} from "@angular/router";
import {Product} from "../../models/product";
import DiscountUtil from "../../_helpers/discount.util";
import {ProductService} from "../../_services/product.service";
import {BrowserDetectorService} from "../../_services/browser-detector.service";
import {Title} from "@angular/platform-browser";
import {NgxSliderModule, Options} from "@angular-slider/ngx-slider";
import {ToastrService} from "ngx-toastr";
import {NgClass} from "@angular/common";
import {FaIconComponent} from "@fortawesome/angular-fontawesome";
import {FormsModule} from "@angular/forms";
import {TranslocoDirective} from "@jsverse/transloco";

@Component({
  selector: 'app-detail',
  templateUrl: './detail.component.html',
  imports: [
    NgClass,
    FaIconComponent,
    NgxSliderModule,
    FormsModule,
    RouterLink,
    TranslocoDirective
  ],
  styleUrls: ['./detail.component.css']
})
export class DetailComponent implements OnInit {
  private readonly cartService = inject(CartService);
  private readonly favoriteService = inject(FavoriteService);
  private readonly route = inject(ActivatedRoute);
  private readonly toastr = inject(ToastrService);
  private readonly productService = inject(ProductService);
  public readonly browserDetect = inject(BrowserDetectorService);
  private readonly titleService = inject(Title);

  product: Product;
  discount_percentage: any;
  quantity: number = 1;
  relatedProducts: Product[];
  private sub: any;
  private id: string;
  sliderOptions: Options = {
    floor: 1,
    ceil: 10
  };

  ngOnInit(): void {
    this.sub = this.route.params.subscribe(params => {
      this.id = params['id'];
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

  getProduct(id: string) {
    this.productService.getProduct(id).subscribe(response => {
      this.product = response;
      this.updateTitle(this.product.name);
      if (this.product.is_location_offer) {
        this.product.discount_price = DiscountUtil.calculateDiscount(this.product.price);
      }
    });
    this.discount_percentage = DiscountUtil.getDiscountPercentage();
  }

  getRelatedProducts(id: string) {
    this.productService.getRelatedProducts(id).subscribe(response => {
      this.relatedProducts = response;
    });
  }

  addToFavorites(product: any) {
    let payload = {product_id: product.id}
    this.favoriteService.addFavorite(payload).subscribe(() => {
      this.toastr.success('Product added to your favorites list.', null, {progressBar: true});
    }, (response) => {
      if (response.error.message === 'Duplicate Entry') {
        this.toastr.error('Product already in your favorites list.', null, {progressBar: true});
      } else if (response.error.message === 'Unauthorized') {
        this.toastr.error('Unauthorized, can not add product to your favorite list.', null, {progressBar: true});
      }
    });
  }

  addToCart(product: Product) {
    if (this.quantity >= 1) {
      const price = (product.discount_price) ? product.discount_price : product.price;
      let item = {
        'id': product.id,
        'quantity': this.quantity,
        'price': price,
        'total': this.quantity * price
      }
      this.cartService.addItem(item).subscribe(() => {
        this.toastr.success('Product added to shopping cart.', null, {progressBar: true});
      }, (response) => {
        this.toastr.error(response.error.message, null, {progressBar: true});
      });
    }
  }

  private updateTitle(productName: string) {
    this.titleService.setTitle(`${productName} - Practice Software Testing - Toolshop - v5.0`);
  }

}
