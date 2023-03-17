import {Component, OnInit} from '@angular/core';
import {ActivatedRoute} from "@angular/router";
import {ToastService} from "../../_services/toast.service";
import {Product} from "../../models/product";
import {ProductService} from "../../_services/product.service";
import {BrowserDetectorService} from "../../_services/browser-detector.service";

@Component({
  selector: 'app-detail',
  templateUrl: './detail.component.html',
  styleUrls: ['./detail.component.css']
})
export class DetailComponent implements OnInit {
  product: Product;
  discount_percentage: any;
  quantity: number = 1;
  relatedProducts: Product[];
  private sub: any;
  private id: number;

  constructor(private route: ActivatedRoute,
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
