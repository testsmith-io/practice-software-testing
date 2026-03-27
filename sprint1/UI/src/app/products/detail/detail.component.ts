import {Component, inject, OnInit} from '@angular/core';
import {ActivatedRoute, RouterLink} from "@angular/router";
import {Product} from "../../models/product";
import {ProductService} from "../../_services/product.service";
import {BrowserDetectorService} from "../../_services/browser-detector.service";
import {NgClass} from "@angular/common";

@Component({
  selector: 'app-detail',
  templateUrl: './detail.component.html',
  imports: [
    NgClass,
    RouterLink
],
  styleUrls: ['./detail.component.css']
})
export class DetailComponent implements OnInit {
  private readonly route = inject(ActivatedRoute);
  private readonly productService = inject(ProductService);
  public readonly browserDetect = inject(BrowserDetectorService);

  product: Product;
  discount_percentage: any;
  quantity: number = 1;
  relatedProducts: Product[];
  private sub: any;
  private id: number;

  ngOnInit(): void {
    this.sub = this.route.params.subscribe(params => {
      this.id = +params['id'];
      this.getProduct(this.id);
      this.getRelatedProducts(this.id);
    });
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

}
