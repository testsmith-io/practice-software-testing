import {Component, inject, OnInit} from '@angular/core';
import {ActivatedRoute, RouterLink} from "@angular/router";
import {Product} from "../../models/product";
import {ProductService} from "../../_services/product.service";
import {BrowserDetectorService} from "../../_services/browser-detector.service";
import {NgClass, TitleCasePipe} from "@angular/common";

@Component({
  selector: 'app-category',
  templateUrl: './category.component.html',
  imports: [
    TitleCasePipe,
    RouterLink,
    NgClass
],
  styleUrls: ['./category.component.css']
})
export class CategoryComponent implements OnInit {
  private readonly productService = inject(ProductService);
  private readonly route = inject(ActivatedRoute);
  public readonly browserDetect = inject(BrowserDetectorService);

  results: Product[];
  slug: string;

  ngOnInit(): void {
    this.route.params.subscribe(params => {
      this.slug = params['name'];
      this.getProductsByCategory(this.slug);
    });
  }

  getProductsByCategory(slug: string) {
    this.productService.getProductsByCategory(slug).subscribe(res => {
      this.results = res;
    });
  }

}
