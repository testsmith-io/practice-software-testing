// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Component, inject, OnDestroy, OnInit} from '@angular/core';
import {CartService} from "../../_services/cart.service";
import {FavoriteService} from "../../_services/favorite.service";
import {ActivatedRoute, RouterLink} from "@angular/router";
import {ToastService} from "../../_services/toast.service";
import {Product} from "../../models/product";
import DiscountUtil from "../../_helpers/discount.util";
import {ProductService} from "../../_services/product.service";
import {NgxSliderModule, Options} from "@angular-slider/ngx-slider";
import {BrowserDetectorService} from "../../_services/browser-detector.service";
import {NgClass} from "@angular/common";
import {FormsModule} from "@angular/forms";
import {Subject} from "rxjs";
import {takeUntil} from "rxjs/operators";

@Component({
  selector: 'app-detail',
  templateUrl: './detail.component.html',
  imports: [
    NgClass,
    RouterLink,
    NgxSliderModule,
    FormsModule
  ],
  styleUrls: ['./detail.component.css']
})
export class DetailComponent implements OnInit, OnDestroy {
  private readonly cartService = inject(CartService);
  private readonly favoriteService = inject(FavoriteService);
  private readonly route = inject(ActivatedRoute);
  private readonly toastService = inject(ToastService);
  private readonly productService = inject(ProductService);
  public readonly browserDetect = inject(BrowserDetectorService);

  product: Product;
  discount_percentage: any;
  quantity: number = 1;
  readonly MAX_QUANTITY = 99;
  relatedProducts: Product[];
  private sub: any;
  private id: number;
  private readonly destroy$ = new Subject<void>();
  sliderOptions: Options = {
    floor: 1,
    ceil: 10
  };

  ngOnInit(): void {
    this.sub = this.route.params.pipe(takeUntil(this.destroy$)).subscribe(params => {
      this.id = +params['id'];
      this.getProduct(this.id);
      this.getRelatedProducts(this.id);
    });
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  plus() {
  }

  minus() {
  }

  getProduct(id: number) {
    this.productService.getProduct(id).pipe(takeUntil(this.destroy$)).subscribe(response => {
      this.product = response;
      if (this.product.is_location_offer) {
        this.product.discount_price = DiscountUtil.calculateDiscount(this.product.price);
      }
    });
    this.discount_percentage = DiscountUtil.getDiscountPercentage();
  }

  getRelatedProducts(id: number) {
    this.productService.getRelatedProducts(id).pipe(takeUntil(this.destroy$)).subscribe(response => {
      this.relatedProducts = response;
    });
  }

  addToFavorites(product: any) {
    let payload = {product_id: product.id}
    this.favoriteService.addFavorite(payload).subscribe(() => {
      this.toastService.show('Oeps, something went wrong.', {classname: 'bg-danger text-light'})
    }, (response) => {
      if (response.error.message === 'Duplicate Entry') {
        this.toastService.show('Product already in your favorites list.', {classname: 'bg-warning text-dark'})
      } else if (response.error.message === 'Unauthorized') {
        this.toastService.show('Unauthorized, can not add product to your favorite list.', {classname: 'bg-danger text-light'})
      }
    });
  }

  validateQuantity(event: Event): void {
    const target = event.target as HTMLInputElement;
    let value = parseInt(target.value, 10);

    if (isNaN(value) || value < 1) {
      value = 1;
    } else if (value > this.MAX_QUANTITY) {
      value = this.MAX_QUANTITY;
      this.toastService.show(`You can order at most ${this.MAX_QUANTITY} of this product.`, {classname: 'bg-warning text-dark'});
    }

    this.quantity = value;
    target.value = value.toString();
  }

  addToCart(product: Product) {
    if (this.quantity >= 1 && this.quantity <= this.MAX_QUANTITY) {
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
      this.toastService.show('Oeps, something went wrong.', {classname: 'bg-danger text-light'})
    }
  }

  isCo2ScaleEnabled(): boolean {
    const setting = window.localStorage.getItem('CO2_SCALE_ENABLED');
    return setting === null || setting === 'true';
  }

  isEcoBadgeEnabled(): boolean {
    if (!this.isCo2ScaleEnabled()) {
      return false;
    }
    const setting = window.localStorage.getItem('ECO_BADGE_ENABLED');
    return setting === null || setting === 'true';
  }

}
