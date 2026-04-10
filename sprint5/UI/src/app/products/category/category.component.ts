// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Component, inject, OnDestroy, OnInit} from '@angular/core';
import {Subject, takeUntil} from "rxjs";
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
import {Title} from "@angular/platform-browser";
import {FaIconComponent} from "@fortawesome/angular-fontawesome";
import {AsyncPipe, NgClass, NgTemplateOutlet, TitleCasePipe} from "@angular/common";
import {PaginationComponent} from "../../pagination/pagination.component";
import {TranslocoDirective} from "@jsverse/transloco";
import {ComparisonService} from "../../_services/comparison.service";
import {ProductSpecService, SpecNameGroup} from "../../_services/product-spec.service";

@Component({
  selector: 'app-category',
  templateUrl: './category.component.html',
  imports: [
    FaIconComponent,
    RouterLink,
    NgClass,
    PaginationComponent,
    NgTemplateOutlet,
    TranslocoDirective,
    TitleCasePipe,
    AsyncPipe
  ],
  styleUrls: ['./category.component.css']
})
export class CategoryComponent implements OnInit, OnDestroy {
  private destroy$ = new Subject<void>();
  private productService = inject(ProductService);
  private formBuilder = inject(FormBuilder);
  private route = inject(ActivatedRoute);
  private brandService = inject(BrandService);
  private categoryService = inject(CategoryService);
  public browserDetect = inject(BrowserDetectorService);
  private titleService = inject(Title);
  public comparisonService = inject(ComparisonService);
  private specService = inject(ProductSpecService);
  search: FormGroup | any;
  resultState: string = '';
  currentPage: number = 1;
  results: Pagination<Product>;
  brands: Brand[];
  categories: any;
  slug: string;
  private brandsFilter: Array<number> = [];
  private categoriesFilter: Array<number> = [];
  private sorting: string = '';
  private ecoFriendlyFilter: boolean = false;
  categoryCheckboxState: Map<number, boolean> = new Map();
  specGroups: SpecNameGroup[] = [];
  specFilters: Map<string, Set<string>> = new Map();

  ngOnInit(): void {
    this.route.params
      .pipe(takeUntil(this.destroy$))
      .subscribe(params => {
        this.slug = params['name'];

        this.getProductsByCategory(this.slug);
        this.specService.getSpecNames()
          .pipe(takeUntil(this.destroy$))
          .subscribe(response => {
            this.specGroups = response;
          });
        this.brandService.getBrands()
          .pipe(takeUntil(this.destroy$))
          .subscribe(response => {
            this.brands = response;
          });

        this.categoryService.getSubCategoriesTreeBySlug(this.slug)
          .pipe(takeUntil(this.destroy$))
          .subscribe(response => {
            this.categories = response;
            this.updateTitle(this.categories[0].name);
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
    this.productService.getProductsByCategory(slug, this.currentPage).subscribe(res => {
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
    this.productService.getProductsByCategoryAndBrand(this.categoriesFilter.toString(), this.brandsFilter.toString(), this.sorting, this.slug, this.ecoFriendlyFilter, this.buildSpecFilterString()).subscribe(res => {
      this.resultState = 'filter_completed';
      this.results = res;
      this.results.data.forEach((item: Product) => {
        if(item.is_location_offer) {
          item.discount_price = DiscountUtil.calculateDiscount(item.price);
        }
      })
    });
  }

  filterByCategory(event: any, categoryId: number, parentId?: number) {
    this.resultState = 'filter_started';
    const isChecked = event.target.checked;

    // Update the checkbox state
    this.categoryCheckboxState.set(categoryId, isChecked);

    // Update the filter array
    if (isChecked) {
      if (!this.categoriesFilter.includes(categoryId)) {
        this.categoriesFilter.push(categoryId);
      }

      // If this is a child being checked and has a parent, check if all siblings are checked
      if (parentId) {
        this.checkParentIfAllChildrenChecked(parentId);
      }

      // If this is a parent being checked, check all children
      const category = this.findCategoryById(categoryId, this.categories);
      if (category && category.sub_categories && category.sub_categories.length > 0) {
        this.checkAllChildren(category);
      }
    } else {
      this.categoriesFilter = this.categoriesFilter.filter(item => item !== categoryId);

      // If this is a child being unchecked, check if parent should be unchecked
      if (parentId) {
        this.uncheckParentIfNoChildrenChecked(parentId);
      }

      // If this is a parent being unchecked, uncheck all children
      const category = this.findCategoryById(categoryId, this.categories);
      if (category && category.sub_categories && category.sub_categories.length > 0) {
        this.uncheckAllChildren(category);
      }
    }

    this.productService.getProductsByCategoryAndBrand(this.categoriesFilter.toString(), this.brandsFilter.toString(), this.sorting, this.slug, this.ecoFriendlyFilter, this.buildSpecFilterString()).subscribe(res => {
      this.resultState = 'filter_completed';
      this.results = res;
      this.results.data.forEach((item: Product) => {
        if(item.is_location_offer) {
          item.discount_price = DiscountUtil.calculateDiscount(item.price);
        }
      })
    });
  }

  onPageChange(page: number) {
    // Handle page change here (e.g., fetch data for the selected page)
    this.currentPage = page;
    this.getProductsByCategory(this.slug);
  }

  changeSorting(event: any) {
    this.sorting = event.target.value;

    this.resultState = 'sorting_started';
    this.productService.getProductsByCategoryAndBrand(this.categoriesFilter.toString(), this.brandsFilter.toString(), this.sorting, this.slug, this.ecoFriendlyFilter, this.buildSpecFilterString()).subscribe(res => {
      this.results = res;
      this.results.data.forEach((item: Product) => {
        this.resultState = 'sorting_completed';
        if(item.is_location_offer) {
          item.discount_price = DiscountUtil.calculateDiscount(item.price);
        }
      })
    });
  }

  private updateTitle(categoryName: string) {
    this.titleService.setTitle(`${categoryName} - Practice Software Testing - Toolshop - v5.0`);
  }

  private findCategoryById(id: number, categories: any[]): any {
    for (const category of categories) {
      if (category.id === id) {
        return category;
      }
      if (category.sub_categories && category.sub_categories.length > 0) {
        const found = this.findCategoryById(id, category.sub_categories);
        if (found) return found;
      }
    }
    return null;
  }

  private checkAllChildren(category: any) {
    if (category.sub_categories) {
      category.sub_categories.forEach((child: any) => {
        this.categoryCheckboxState.set(child.id, true);
        if (!this.categoriesFilter.includes(child.id)) {
          this.categoriesFilter.push(child.id);
        }
        this.checkAllChildren(child);
      });
    }
  }

  private uncheckAllChildren(category: any) {
    if (category.sub_categories) {
      category.sub_categories.forEach((child: any) => {
        this.categoryCheckboxState.set(child.id, false);
        this.categoriesFilter = this.categoriesFilter.filter(item => item !== child.id);
        this.uncheckAllChildren(child);
      });
    }
  }

  private checkParentIfAllChildrenChecked(parentId: number) {
    const parent = this.findCategoryById(parentId, this.categories);
    if (parent && parent.sub_categories) {
      const allChildrenChecked = parent.sub_categories.every((child: any) =>
        this.categoryCheckboxState.get(child.id) === true
      );

      if (allChildrenChecked) {
        this.categoryCheckboxState.set(parentId, true);
        if (!this.categoriesFilter.includes(parentId)) {
          this.categoriesFilter.push(parentId);
        }
      }
    }
  }

  private uncheckParentIfNoChildrenChecked(parentId: number) {
    const parent = this.findCategoryById(parentId, this.categories);
    if (parent && parent.sub_categories) {
      const anyChildChecked = parent.sub_categories.some((child: any) =>
        this.categoryCheckboxState.get(child.id) === true
      );

      if (!anyChildChecked) {
        this.categoryCheckboxState.set(parentId, false);
        this.categoriesFilter = this.categoriesFilter.filter(item => item !== parentId);

        // Also check if this parent has a parent (grandparent)
        if (parent.parent_id) {
          this.uncheckParentIfNoChildrenChecked(parent.parent_id);
        }
      }
    }
  }

  isCategoryChecked(categoryId: number): boolean {
    return this.categoryCheckboxState.get(categoryId) || false;
  }

  filterByEcoFriendly(event: any) {
    this.resultState = 'filter_started';
    this.ecoFriendlyFilter = event.target.checked;

    this.productService.getProductsByCategoryAndBrand(this.categoriesFilter.toString(), this.brandsFilter.toString(), this.sorting, this.slug, this.ecoFriendlyFilter, this.buildSpecFilterString()).subscribe(res => {
      this.resultState = 'filter_completed';
      this.results = res;
      this.results.data.forEach((item: Product) => {
        if (item.is_location_offer) {
          item.discount_price = DiscountUtil.calculateDiscount(item.price);
        }
      });
    });
  }

  filterBySpec(event: any, specName: string, specValue: string) {
    if (!this.specFilters.has(specName)) {
      this.specFilters.set(specName, new Set());
    }
    const values = this.specFilters.get(specName)!;
    if (event.target.checked) {
      values.add(specValue);
    } else {
      values.delete(specValue);
      if (values.size === 0) this.specFilters.delete(specName);
    }
    this.resultState = 'filter_started';
    this.productService.getProductsByCategoryAndBrand(this.categoriesFilter.toString(), this.brandsFilter.toString(), this.sorting, this.slug, this.ecoFriendlyFilter, this.buildSpecFilterString()).subscribe(res => {
      this.resultState = 'filter_completed';
      this.results = res;
      this.results.data.forEach((item: Product) => {
        if (item.is_location_offer) {
          item.discount_price = DiscountUtil.calculateDiscount(item.price);
        }
      });
    });
  }

  buildSpecFilterString(): string {
    const parts: string[] = [];
    this.specFilters.forEach((values, name) => {
      if (values.size > 0) {
        parts.push(`${name}:${Array.from(values).join('|')}`);
      }
    });
    return parts.join(',');
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
