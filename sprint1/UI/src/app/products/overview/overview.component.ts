import {Component, ElementRef, OnInit, QueryList, ViewChildren} from '@angular/core';
import {Product} from "../../models/product";
import {ProductService} from "../../_services/product.service";
import {BrowserDetectorService} from "../../_services/browser-detector.service";

@Component({
  selector: 'app-overview',
  templateUrl: './overview.component.html',
  styleUrls: ['./overview.component.css']
})
export class OverviewComponent implements OnInit {

  @ViewChildren("checkboxes") checkboxes: QueryList<ElementRef>;

  results: Product[];

  constructor(private productService: ProductService,
              public browserDetect: BrowserDetectorService) {
  }

  ngOnInit(): void {
    this.getProducts();
  }

  getProducts() {
    this.productService.getProducts().subscribe(res => {
      this.results = res;
    });
  }

}
