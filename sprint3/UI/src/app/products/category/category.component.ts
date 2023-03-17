import {Component, OnInit} from '@angular/core';
import {FormBuilder, FormGroup, Validators} from "@angular/forms";
import {Brand} from "../../models/brand";
import {BrandService} from "../../_services/brand.service";
import {CategoryService} from "../../_services/category.service";
import {ActivatedRoute} from "@angular/router";
import {Product} from "../../models/product";
import {ProductService} from "../../_services/product.service";
import {Pagination} from "../../models/pagination";
import {BrowserDetectorService} from "../../_services/browser-detector.service";

@Component({
  selector: 'app-category',
  templateUrl: './category.component.html',
  styleUrls: ['./category.component.css']
})
export class CategoryComponent implements OnInit {
  search: FormGroup | any;
  resultState: string = '';
  p: number = 1;
  results: Pagination<Product>;
  brands: Brand[];
  categories: any;
  slug: string;
  private brandsFilter: Array<number> = [];
  private categoriesFilter: Array<number> = [];
  private sorting: string = '';

  constructor(private productService: ProductService,
              private formBuilder: FormBuilder,
              private route: ActivatedRoute,
              private brandService: BrandService,
              private categoryService: CategoryService,
              public browserDetect: BrowserDetectorService) {
  }

  ngOnInit(): void {
    this.route.params.subscribe(params => {
      this.slug = params['name'];
      this.getProductsByCategory(this.slug);
      this.brandService.getBrands().subscribe(response => {
        this.brands = response;
      });

      this.categoryService.getSubCategoriesTreeBySlug(this.slug).subscribe(response => {
        this.categories = response;
      });
    });

    this.search = this.formBuilder.group(
      {
        query: ['', [Validators.required,
          Validators.minLength(3),
          Validators.maxLength(40)]],
      });
  }

  getProductsByCategory(slug: string) {
    this.productService.getProductsByCategory(slug, this.p).subscribe(res => {
      this.results = res;
    });
  }

  filterByBrand(event: any) {
    this.resultState = 'filter_started';
    if (event.target.checked) {
      this.brandsFilter.push(event.target.value);
    } else {
      this.brandsFilter = this.brandsFilter.filter(item => item !== event.target.value);
    }
    this.productService.getProductsByCategoryAndBrand(this.categoriesFilter.toString(), this.brandsFilter.toString(), this.sorting, this.slug).subscribe(res => {
      this.resultState = 'filter_completed';
      this.results = res;
    });
  }

  filterByCategory(event: any) {
    this.resultState = 'filter_started';
    if (event.target.checked) {
      this.categoriesFilter.push(event.target.value);
    } else {
      this.categoriesFilter = this.categoriesFilter.filter(item => item !== event.target.value);
    }
    this.productService.getProductsByCategoryAndBrand(this.categoriesFilter.toString(), this.brandsFilter.toString(), this.sorting, this.slug).subscribe(res => {
      this.resultState = 'filter_completed';
      this.results = res;
    });
  }

  handlePageChange(event: number): void {
    this.p = event;
    this.getProductsByCategory(this.slug);
  }

  changeSorting(event: any) {
    this.sorting = event.target.value;

    this.resultState = 'sorting_started';
    this.productService.getProductsByCategoryAndBrand(this.categoriesFilter.toString(), this.brandsFilter.toString(), this.sorting, this.slug).subscribe(res => {
      this.results = res;
      this.resultState = 'sorting_completed';
    });
  }


}
