// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Component, ElementRef, inject, OnInit, QueryList, ViewChildren} from '@angular/core';
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
import {TranslocoDirective} from "@jsverse/transloco";
import {NgxSliderModule, Options} from "@angular-slider/ngx-slider";
import {PaginationComponent} from "../../pagination/pagination.component";
import {NgClass, NgTemplateOutlet} from "@angular/common";
import {RouterLink} from "@angular/router";
import { debounceTime, distinctUntilChanged, filter } from 'rxjs/operators';

@Component({
  selector: 'app-overview',
  templateUrl: './overview.component.html',
  imports: [
    FaIconComponent,
    TranslocoDirective,
    ReactiveFormsModule,
    NgxSliderModule,
    PaginationComponent,
    NgClass,
    RouterLink,
    NgTemplateOutlet
  ],
  styleUrls: ['./overview.component.css']
})
export class OverviewComponent implements OnInit {
  private readonly productService = inject(ProductService);
  private readonly formBuilder = inject(FormBuilder);
  private readonly brandService = inject(BrandService);
  private readonly categoryService = inject(CategoryService);
  public browserDetect = inject(BrowserDetectorService);

  @ViewChildren("checkboxes") checkboxes: QueryList<ElementRef>;

  search: FormGroup;
  resultState: string = '';
  currentPage: number = 1;
  results: Pagination<Product>;
  itemsToLoad = Array.from({ length: 8 }, (_, i) => ({ id: i }));
  brands: Brand[];
  categories: Category[];
  private brandsFilter: Array<string> = [];
  private categoriesFilter: Array<number> = [];
  private sorting: string = '';
  private ecoFriendlyFilter: boolean = false;
  categoryCheckboxState: Map<number, boolean> = new Map();
  searchQuery: string;
  minPrice: number = 1;
  maxPrice: number = 100;
  sliderOptions: Options = {
    floor: 0,
    ceil: 200
  };

  ngOnInit(): void {
    this.getProducts();

    this.brandService.getBrands().subscribe(response => {
      this.brands = response;
    });

    this.categoryService.getCategoriesTree().subscribe(response => {
      this.categories = response;
    });

    this.search = this.formBuilder.group({
      query: ['', [Validators.required, Validators.minLength(3), Validators.maxLength(40)]],
    });
    
    this.search.get('query')?.valueChanges.pipe(
      debounceTime(300),
      distinctUntilChanged(),
      filter((value: string) => value.length >= 2)
    ).subscribe((query: string) => {
      this.triggerSearch(query);
    });

  }


  onPageChange(page: number) {
    this.currentPage = page;
    this.getProducts();
  }

  getProducts() {
    this.productService.getProductsNew(this.searchQuery, this.sorting, this.minPrice.toString(), this.maxPrice.toString(), this.categoriesFilter.toString(), this.brandsFilter.toString(), this.currentPage, this.ecoFriendlyFilter).subscribe(res => {
      this.results = res;
      this.results.data.forEach((item: Product) => {
        if (item.is_location_offer) {
          item.discount_price = DiscountUtil.calculateDiscount(item.price);
        }
      })
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

  selectParentWithSubcategories(parentCategory: Category, event: Event) {
    const isChecked = (event.target as HTMLInputElement).checked;

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

  filterByCategory(event: Event, categoryId?: number, parentId?: number) {
    this.resultState = 'filter_started';
    const input = event.target as HTMLInputElement;
    const isChecked = input.checked;
    const catId = categoryId || Number(input.value);

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
      if (category?.sub_categories && category.sub_categories.length > 0) {
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
      if (category?.sub_categories && category.sub_categories.length > 0) {
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

  triggerSearch(query: string): void {
    this.resultState = 'search_started';
    this.searchQuery = query;

    this.productService.searchProducts(query).subscribe(res => {
      this.resultState = 'search_completed';
      this.minPrice = 1;
      this.maxPrice = 100;
      this.sorting = null;
      this.brandsFilter = [];
      this.categoriesFilter = [];
      this.uncheckAll();
      this.results = res;
    });
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
    this.ecoFriendlyFilter = false;
    this.uncheckAll();
    this.productService.getProductsNew(this.searchQuery, this.sorting, this.minPrice.toString(), this.maxPrice.toString(), this.categoriesFilter.toString(), this.brandsFilter.toString(), 0, this.ecoFriendlyFilter).subscribe(res => {
      this.results = res;
    });
  }

  changeSorting(event: Event) {
    this.sorting = (event.target as HTMLSelectElement).value;

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

  filterByEcoFriendly(event: Event) {
    this.resultState = 'filter_started';
    this.ecoFriendlyFilter = (event.target as HTMLInputElement).checked;

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

  uncheckAll() {
    this.checkboxes.forEach((element) => {
      element.nativeElement.checked = false;
    });
    this.categoryCheckboxState.clear();
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
    if (parent?.sub_categories) {
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
    if (parent?.sub_categories) {
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
