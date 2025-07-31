import {Component, ElementRef, inject, OnInit, QueryList, ViewChildren} from '@angular/core';
import {Product} from "../../models/product";
import {ProductService} from "../../_services/product.service";
import {BrowserDetectorService} from "../../_services/browser-detector.service";
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
  public readonly browserDetect = inject(BrowserDetectorService);

  @ViewChildren("checkboxes") checkboxes: QueryList<ElementRef>;

  results: Product[];

  ngOnInit(): void {
    this.getProducts();
  }

  getProducts() {
    this.productService.getProducts().subscribe(res => {
      this.results = res;
    });
  }

}
