import {Component, inject, OnInit} from '@angular/core';
import {Product} from "../../../models/product";
import DiscountUtil from "../../../_helpers/discount.util";
import {ProductService} from "../../../_services/product.service";
import {Pagination} from "../../../models/pagination";
import {BrowserDetectorService} from "../../../_services/browser-detector.service";
import {RouterLink} from "@angular/router";
import {NgClass} from "@angular/common";

@Component({
  selector: 'app-overview',
  templateUrl: './overview.component.html',
  imports: [
    RouterLink,
    NgClass
  ],
  styleUrls: ['./overview.component.css']
})
export class OverviewComponent implements OnInit {
  private readonly productService = inject(ProductService);
  protected readonly browserDetect = inject(BrowserDetectorService);

  p: number = 1;
  results: Pagination<Product>;

  ngOnInit(): void {
    this.getProductRentals();
  }

  getProductRentals() {
    this.productService.getProductRentals().subscribe(res => {
      this.results = res;
      this.results.data.map((item: Product) => {
        if (item.is_location_offer) {
          item.discount_price = DiscountUtil.calculateDiscount(item.price);
        }
      })
    });
  }

}
