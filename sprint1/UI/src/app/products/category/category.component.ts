import {Component, OnInit} from '@angular/core';
import {FormBuilder} from "@angular/forms";
import {ActivatedRoute} from "@angular/router";
import {Product} from "../../models/product";
import {ProductService} from "../../_services/product.service";
import {BrowserDetectorService} from "../../_services/browser-detector.service";

@Component({
  selector: 'app-category',
  templateUrl: './category.component.html',
  styleUrls: ['./category.component.css']
})
export class CategoryComponent implements OnInit {
  results: Product[];
  slug: string;

  constructor(private productService: ProductService,
              private formBuilder: FormBuilder,
              private route: ActivatedRoute,
              public browserDetect: BrowserDetectorService) {
  }

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
