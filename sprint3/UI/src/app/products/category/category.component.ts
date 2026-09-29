// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Component, inject, OnDestroy, OnInit} from '@angular/core';
import {FormBuilder, FormGroup, Validators} from "@angular/forms";
import {Brand} from "../../models/brand";
import {BrandService} from "../../_services/brand.service";
import {CategoryService} from "../../_services/category.service";
import {ActivatedRoute, RouterLink} from "@angular/router";
import {Product} from "../../models/product";
import {Category} from "../../models/category";
import {ProductService} from "../../_services/product.service";
import {Pagination} from "../../models/pagination";
import {BrowserDetectorService} from "../../_services/browser-detector.service";
import {NgClass, NgTemplateOutlet, TitleCasePipe} from "@angular/common";
import {NgxPaginationModule} from "ngx-pagination";
import {Subject} from "rxjs";
import {takeUntil} from "rxjs/operators";

@Component({
  selector: 'app-category',
  templateUrl: './category.component.html',
  imports: [
    NgTemplateOutlet,
    NgxPaginationModule,
    RouterLink,
    NgClass,
    TitleCasePipe
],
  styleUrls: ['./category.component.css']
})
export class CategoryComponent implements OnInit, OnDestroy {
  private readonly destroy$ = new Subject<void>();

  private readonly productService = inject(ProductService);
  private readonly formBuilder = inject(FormBuilder);
  private readonly route = inject(ActivatedRoute);
  private readonly brandService = inject(BrandService);
  private readonly categoryService = inject(CategoryService);
  public readonly browserDetect = inject(BrowserDetectorService);

  search: FormGroup;
  resultState: string = '';
  p: number = 1;
  results: Pagination<Product>;
  brands: Brand[];
  categories: Category[];
  slug: string;
  private brandsFilter: Array<string> = [];
  private categoriesFilter: Array<string> = [];
  private sorting: string = '';

  constructor() {
  }

  ngOnInit(): void {
    this.route.params.pipe(takeUntil(this.destroy$)).subscribe(params => {
      this.slug = params['name'];
      this.getProductsByCategory(this.slug);
      this.brandService.getBrands().pipe(takeUntil(this.destroy$)).subscribe(response => {
        this.brands = response;
      });

      this.categoryService.getSubCategoriesTreeBySlug(this.slug).pipe(takeUntil(this.destroy$)).subscribe(response => {
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

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  getProductsByCategory(slug: string) {
    this.productService.getProductsByCategory(slug, this.p).pipe(takeUntil(this.destroy$)).subscribe(res => {
      this.results = res;
    });
  }

  filterByBrand(event: Event) {
    const input = event.target as HTMLInputElement;
    this.resultState = 'filter_started';
    if (input.checked) {
      this.brandsFilter.push(input.value);
    } else {
      this.brandsFilter = this.brandsFilter.filter(item => item !== input.value);
    }
    this.productService.getProductsByCategoryAndBrand(this.categoriesFilter.toString(), this.brandsFilter.toString(), this.sorting, this.slug).pipe(takeUntil(this.destroy$)).subscribe(res => {
      this.resultState = 'filter_completed';
      this.results = res;
    });
  }

  filterByCategory(event: Event) {
    const input = event.target as HTMLInputElement;
    this.resultState = 'filter_started';
    if (input.checked) {
      this.categoriesFilter.push(input.value);
    } else {
      this.categoriesFilter = this.categoriesFilter.filter(item => item !== input.value);
    }
    this.productService.getProductsByCategoryAndBrand(this.categoriesFilter.toString(), this.brandsFilter.toString(), this.sorting, this.slug).pipe(takeUntil(this.destroy$)).subscribe(res => {
      this.resultState = 'filter_completed';
      this.results = res;
    });
  }

  handlePageChange(event: number): void {
    this.p = event;
    this.getProductsByCategory(this.slug);
  }

  changeSorting(event: Event) {
    this.sorting = (event.target as HTMLSelectElement).value;

    this.resultState = 'sorting_started';
    this.productService.getProductsByCategoryAndBrand(this.categoriesFilter.toString(), this.brandsFilter.toString(), this.sorting, this.slug).pipe(takeUntil(this.destroy$)).subscribe(res => {
      this.results = res;
      this.resultState = 'sorting_completed';
    });
  }


}
