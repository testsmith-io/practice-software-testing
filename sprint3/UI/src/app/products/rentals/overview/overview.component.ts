import {Component, OnInit} from '@angular/core';
import {Product} from "../../../models/product";
import {ProductService} from "../../../_services/product.service";
import {Pagination} from "../../../models/pagination";
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

  p: number = 1;
  results: Pagination<Product>;

  constructor(public productService: ProductService) {
  }

  ngOnInit(): void {
    this.getProductRentals();
  }

  getProductRentals() {
    this.productService.getProductRentals().subscribe(res => {
      this.results = res;
    });
  }

}
