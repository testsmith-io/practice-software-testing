import {Component, inject, OnInit} from '@angular/core';
import {Product} from "../../../models/product";
import {ProductService} from "../../../_services/product.service";
import {Pagination} from "../../../models/pagination";
import {NgClass} from "@angular/common";
import {RouterLink} from "@angular/router";

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

  p: number = 1;
  results: Pagination<Product>;

  ngOnInit(): void {
    this.getProductRentals();
  }

  getProductRentals() {
    this.productService.getProductRentals().subscribe(res => {
      this.results = res;
    });
  }

}
