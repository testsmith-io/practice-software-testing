import {Component, OnInit} from '@angular/core';
import {Product} from "../../../models/product";
import DiscountUtil from "../../../_helpers/discount.util";
import {ProductService} from "../../../_services/product.service";
import {Pagination} from "../../../models/pagination";
import {BrowserDetectorService} from "../../../_services/browser-detector.service";

@Component({
  selector: 'app-overview',
  templateUrl: './overview.component.html',
  styleUrls: ['./overview.component.css']
})
export class OverviewComponent implements OnInit {

  p: number = 1;
  results: Pagination<Product>;

  constructor(public productService: ProductService,
              public browserDetect: BrowserDetectorService) {
  }

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
