// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Component, ElementRef, inject, OnDestroy, OnInit, QueryList, ViewChildren} from '@angular/core';
import {Subject, takeUntil} from "rxjs";
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from "@angular/forms";
import {Brand} from "../../models/brand";
import {BrandService} from "../../_services/brand.service";
import {CategoryService} from "../../_services/category.service";
import {Product} from "../../models/product";
import DiscountUtil from "../../_helpers/discount.util";
import {Pagination} from "../../models/pagination";
import {ProductService} from "../../_services/product.service";
import {BrowserDetectorService} from "../../_services/browser-detector.service";
import {Category} from "../../models/category";
import {FaIconComponent} from "@fortawesome/angular-fontawesome";
import {AsyncPipe, NgClass, NgTemplateOutlet} from "@angular/common";
import {PaginationComponent} from "../../pagination/pagination.component";
import {RouterLink} from "@angular/router";
import {NgxSliderModule} from "@angular-slider/ngx-slider";
import {TranslocoDirective} from "@jsverse/transloco";
import {ComparisonService} from "../../_services/comparison.service";
import {ProductSpecService, SpecNameGroup} from "../../_services/product-spec.service";

@Component({
  selector: 'app-overview',
  templateUrl: './overview.component.html',
  imports: [
    FaIconComponent,
    NgClass,
    PaginationComponent,
    RouterLink,
    ReactiveFormsModule,
    NgxSliderModule,
    NgTemplateOutlet,
    TranslocoDirective,
    AsyncPipe
  ],
  styleUrls: ['./overview.component.css']
})
export class OverviewComponent implements OnInit, OnDestroy {
  private destroy$ = new Subject<void>();
  private productService = inject(ProductService);
  private formBuilder = inject(FormBuilder);
  private brandService = inject(BrandService);
  private categoryService = inject(CategoryService);
  public browserDetect = inject(BrowserDetectorService);
  public comparisonService = inject(ComparisonService);
  private specService = inject(ProductSpecService);

  @ViewChildren("checkboxes") checkboxes: QueryList<ElementRef>;

  search: FormGroup | any;
  resultState: string = '';
  currentPage: number = 1;
  results: Pagination<Product>;
  itemsToLoad = Array.from({ length: 8 }, (_, i) => ({ id: i }));
  brands: Brand[];
  categories: Category[];
  private brandsFilter: Array<number> = [];
  private categoriesFilter: Array<number> = [];
  private sorting: string = '';
  private ecoFriendlyFilter: boolean = false;
  categoryCheckboxState: Map<number, boolean> = new Map();
  searchQuery: string;
  specGroups: SpecNameGroup[] = [];
  specFilters: Map<string, Set<string>> = new Map();
  minPrice: number = 1;
  maxPrice: number = 100;
  sliderOptions: any = {
    floor: 0,
    ceil: 200
  };

  ngOnInit(): void {
    this.getProducts();

    this.brandService.getBrands()
      .pipe(takeUntil(this.destroy$))
      .subscribe(response => {
        this.brands = response;
        });

    this.categoryService.getCategoriesTree()
      .pipe(takeUntil(this.destroy$))
      .subscribe(response => {
        this.categories = response;
        });

    this.specService.getSpecNames()
      .pipe(takeUntil(this.destroy$))
      .subscribe(response => {
        this.specGroups = response;
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

  onPageChange(page: number) {
    this.currentPage = page;
    this.getProducts();
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

  filterByEcoFriendly(event: any) {
    this.ecoFriendlyFilter = event.target.checked;
    this.currentPage = 0;
    this.getProducts();
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
    this.currentPage = 0;
    this.getProducts();
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

  getProducts() {
    this.productService.getProductsNew(this.searchQuery, this.sorting, this.minPrice.toString(), this.maxPrice.toString(), this.categoriesFilter.toString(), this.brandsFilter.toString(), this.currentPage, this.ecoFriendlyFilter, false, this.buildSpecFilterString()).subscribe(res => {
      this.results = res;
      this.results.data.forEach((item: Product) => {
        if (item.is_location_offer) {
          item.discount_price = DiscountUtil.calculateDiscount(item.price);
        }
      });
    });
  }

  filterByBrand(event: any) {
    this.resultState = 'filter_started';
    if (event.target.checked) {
      this.brandsFilter.push(event.target.value);
    } else {
      this.brandsFilter = this.brandsFilter.filter(item => item !== event.target.value);
    }
    this.productService.getProductsNew(this.searchQuery, this.sorting, this.minPrice.toString(), this.maxPrice.toString(), this.categoriesFilter.toString(), this.brandsFilter.toString(), 0, this.ecoFriendlyFilter).subscribe(res => {
      this.resultState = 'filter_completed';
      this.currentPage = 1;
      this.results = res;
      this.results.data.forEach((item: Product) => {
        if (item.is_location_offer) {
          item.discount_price = DiscountUtil.calculateDiscount(item.price);
        }
      });
    });
  }

  selectParentWithSubcategories(parentCategory: any, event: any) {
    const isChecked = event.target.checked;

    this.categoryCheckboxState.set(parentCategory.id, isChecked);
    this.updateCategoryFilter(parentCategory.id, isChecked);
    this.updateSubcategories(parentCategory, isChecked);

    this.filterProducts();
  }

  updateSubcategories(category: Category, isChecked: boolean) {
    category.sub_categories.forEach((subCat: Category) => {
      this.categoryCheckboxState.set(subCat.id, isChecked);
      this.updateCategoryFilter(subCat.id, isChecked);
      if (subCat.sub_categories && subCat.sub_categories.length > 0) {
        this.updateSubcategories(subCat, isChecked);
      }
    });
  }

  updateCategoryFilter(categoryId: number, addCategory: boolean) {
    if (addCategory) {
      if (!this.categoriesFilter.includes(categoryId)) {
        this.categoriesFilter.push(categoryId);
      }
    } else {
      this.categoriesFilter = this.categoriesFilter.filter(item => item !== categoryId);
    }
  }

  isCategorySelected(category: Category): boolean {
    return this.categoryCheckboxState.get(category.id) || this.categoriesFilter.includes(category.id);
  }

  filterProducts() {
    this.productService.getProductsNew(this.searchQuery, this.sorting, this.minPrice.toString(), this.maxPrice.toString(), this.categoriesFilter.toString(), this.brandsFilter.toString(), 0, this.ecoFriendlyFilter).subscribe(res => {
      this.resultState = 'filter_completed';
      this.currentPage = 1;
      this.results = res;
      this.results.data.forEach((item: Product) => {
        if (item.is_location_offer) {
          item.discount_price = DiscountUtil.calculateDiscount(item.price);
        }
      });
    });
  }

  filterByCategory(event: any, categoryId?: number, parentId?: number) {
    this.resultState = 'filter_started';
    const isChecked = event.target.checked;
    const catId = categoryId || Number(event.target.value);

    this.categoryCheckboxState.set(catId, isChecked);

    if (isChecked) {
      if (!this.categoriesFilter.includes(catId)) {
        this.categoriesFilter.push(catId);
      }

      // If this is a child being checked, check if all siblings are checked to check parent
      if (parentId) {
        this.checkParentIfAllChildrenChecked(parentId);
      }

      // If this is a parent being checked, check all children
      const category = this.findCategoryById(catId);
      if (category && category.sub_categories && category.sub_categories.length > 0) {
        this.updateSubcategories(category, true);
      }
    } else {
      this.categoriesFilter = this.categoriesFilter.filter(item => item !== catId);

      // If this is a child being unchecked, check if parent should be unchecked
      if (parentId) {
        this.uncheckParentIfNoChildrenChecked(parentId);
      }

      // If this is a parent being unchecked, uncheck all children
      const category = this.findCategoryById(catId);
      if (category && category.sub_categories && category.sub_categories.length > 0) {
        this.updateSubcategories(category, false);
      }
    }

    this.productService.getProductsNew(this.searchQuery, this.sorting, this.minPrice.toString(), this.maxPrice.toString(), this.categoriesFilter.toString(), this.brandsFilter.toString(), 0, this.ecoFriendlyFilter).subscribe(res => {
      this.resultState = 'filter_completed';
      this.currentPage = 1;
      this.results = res;
      this.results.data.forEach((item: Product) => {
        if (item.is_location_offer) {
          item.discount_price = DiscountUtil.calculateDiscount(item.price);
        }
      })
    });
  }

  onSearchSubmit() {
    if (this.search.invalid) {
      return;
    }

    this.resultState = 'search_started';
    this.searchQuery = this.search.value.query;
    this.productService.searchProducts(this.searchQuery).subscribe(res => {
      this.resultState = 'search_completed';
      this.minPrice = 1;
      this.maxPrice = 100;
      this.sorting = null;
      this.brandsFilter = [];
      this.categoriesFilter = [];
      this.uncheckAll();
      this.results = res;
    });
    this.search.reset();
    this.uncheckAll();
  }

  changePriceRange() {
    this.productService.getProductsNew(this.searchQuery, this.sorting, this.minPrice.toString(), this.maxPrice.toString(), this.categoriesFilter.toString(), this.brandsFilter.toString(), 0, this.ecoFriendlyFilter).subscribe(res => {
      this.results = res;
    });
  }

  reset() {
    this.minPrice = 1;
    this.maxPrice = 100;
    this.searchQuery = null;
    this.sorting = null;
    this.brandsFilter = [];
    this.categoriesFilter = [];
    this.uncheckAll();
    this.productService.getProductsNew(this.searchQuery, this.sorting, this.minPrice.toString(), this.maxPrice.toString(), this.categoriesFilter.toString(), this.brandsFilter.toString(), 0, this.ecoFriendlyFilter).subscribe(res => {
      this.results = res;
    });
  }

  changeSorting(event: any) {
    this.sorting = event.target.value;

    this.resultState = 'sorting_started';
    this.productService.getProductsNew(this.searchQuery, this.sorting, this.minPrice.toString(), this.maxPrice.toString(), this.categoriesFilter.toString(), this.brandsFilter.toString(), 0, this.ecoFriendlyFilter).subscribe(res => {
      this.results = res;
      this.results.data.forEach((item: Product) => {
        this.resultState = 'sorting_completed';
        if (item.is_location_offer) {
          item.discount_price = DiscountUtil.calculateDiscount(item.price);
        }
      })
    });
  }

  uncheckAll() {
    this.checkboxes.forEach((element) => {
      element.nativeElement.checked = false;
    });
    this.categoryCheckboxState.clear();
  }

  private findCategoryById(id: number, categories?: Category[]): Category | null {
    const searchCategories = categories || this.categories;
    for (const category of searchCategories) {
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

  private checkParentIfAllChildrenChecked(parentId: number) {
    const parent = this.findCategoryById(parentId);
    if (parent && parent.sub_categories) {
      const allChildrenChecked = parent.sub_categories.every((child: Category) =>
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
    const parent = this.findCategoryById(parentId);
    if (parent && parent.sub_categories) {
      const anyChildChecked = parent.sub_categories.some((child: Category) =>
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

}
