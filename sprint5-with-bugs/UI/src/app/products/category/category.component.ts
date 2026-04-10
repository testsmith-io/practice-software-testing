// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Component, inject, OnDestroy, OnInit} from '@angular/core';
import {FormBuilder, FormGroup, Validators} from "@angular/forms";
import {Brand} from "../../models/brand";
import {BrandService} from "../../_services/brand.service";
import {CategoryService} from "../../_services/category.service";
import {ActivatedRoute, RouterLink} from "@angular/router";
import {Product} from "../../models/product";
import DiscountUtil from "../../_helpers/discount.util";
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
    NgClass,
    TitleCasePipe,
    NgxPaginationModule,
    NgTemplateOutlet,
    RouterLink
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
  private ecoFriendlyFilter: boolean = false;
  protected readonly Math = Math;

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
      this.results.data.forEach((item: Product) => {
        if(item.is_location_offer) {
          item.discount_price = DiscountUtil.calculateDiscount(item.price);
        }
      })
    });
  }

  filterByBrand(event: any) {
    this.resultState = 'filter_started';
    if (event.target.checked) {
      this.brandsFilter.push(event.target.value);
    } else {
      this.brandsFilter = this.brandsFilter.filter(item => item !== event.target.value);
    }
    this.productService.getProductsByCategoryAndBrand(this.categoriesFilter.toString(), this.brandsFilter.toString(), this.sorting, this.slug, this.ecoFriendlyFilter).pipe(takeUntil(this.destroy$)).subscribe(res => {
      this.resultState = 'filter_completed';
      this.results = res;
      this.results.data.forEach((item: Product) => {
        if(item.is_location_offer) {
          item.discount_price = DiscountUtil.calculateDiscount(item.price);
        }
      })
    });
  }

  filterByCategory(event: any) {
    this.resultState = 'filter_started';
    if (event.target.checked) {
      this.categoriesFilter.push(event.target.value);
    } else {
      this.categoriesFilter = this.categoriesFilter.filter(item => item !== event.target.value);
    }
    this.productService.getProductsByCategoryAndBrand(this.categoriesFilter.toString(), this.brandsFilter.toString(), this.sorting, this.slug, this.ecoFriendlyFilter).pipe(takeUntil(this.destroy$)).subscribe(res => {
      this.resultState = 'filter_completed';
      this.results = res;
      this.results.data.forEach((item: Product) => {
        if(item.is_location_offer) {
          item.discount_price = DiscountUtil.calculateDiscount(item.price);
        }
      })
    });
  }

  handlePageChange(event: number): void {
    this.p = event;
    this.getProductsByCategory(this.slug);
  }

  changeSorting(event: any) {
    this.sorting = event.target.value;

    this.resultState = 'sorting_started';
    this.productService.getProductsByCategoryAndBrand(this.categoriesFilter.toString(), this.brandsFilter.toString(), this.sorting, this.slug, this.ecoFriendlyFilter).pipe(takeUntil(this.destroy$)).subscribe(res => {
      this.results = res;
      this.results.data.forEach((item: Product) => {
        this.resultState = 'sorting_completed';
        if(item.is_location_offer) {
          item.discount_price = DiscountUtil.calculateDiscount(item.price);
        }
      })
    });
  }

  filterByEcoFriendly(event: any) {
    this.resultState = 'filter_started';
    this.ecoFriendlyFilter = event.target.checked;

    this.productService.getProductsByCategoryAndBrand(this.categoriesFilter.toString(), this.brandsFilter.toString(), this.sorting, this.slug, this.ecoFriendlyFilter).pipe(takeUntil(this.destroy$)).subscribe(res => {
      this.resultState = 'filter_completed';
      this.p = 1;
      this.results = res;
      this.results.data.forEach((item: Product) => {
        if (item.is_location_offer) {
          item.discount_price = DiscountUtil.calculateDiscount(item.price);
        }
      });
    });
  }

  isCo2ScaleEnabled(): boolean {
    const setting = window.localStorage.getItem('CO2_SCALE_ENABLED');
    return setting === null || setting === 'true';
  }

  isEcoBadgeEnabled(): boolean {
    if (!this.isCo2ScaleEnabled()) {
      return false;
    }
    const setting = window.localStorage.getItem('ECO_BADGE_ENABLED');
    return setting === null || setting === 'true';
  }

}
