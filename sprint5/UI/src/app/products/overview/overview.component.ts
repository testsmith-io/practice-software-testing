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
import {NgClass, NgTemplateOutlet} from "@angular/common";
import {PaginationComponent} from "../../pagination/pagination.component";
import {RouterLink} from "@angular/router";
import {NgxSliderModule} from "@angular-slider/ngx-slider";
import {TranslocoDirective} from "@jsverse/transloco";

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
    TranslocoDirective
  ],
  styleUrls: ['./overview.component.css']
})
export class OverviewComponent implements OnInit {
  private productService = inject(ProductService);
  private formBuilder = inject(FormBuilder);
  private brandService = inject(BrandService);
  private categoryService = inject(CategoryService);
  public browserDetect = inject(BrowserDetectorService);

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
  searchQuery: string;
  minPrice: number = 1;
  maxPrice: number = 100;
  sliderOptions: any = {
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

    this.search = this.formBuilder.group(
      {
        query: ['', [Validators.required,
          Validators.minLength(3),
          Validators.maxLength(40)]],
      });
  }

  onPageChange(page: number) {
    this.currentPage = page;
    this.getProducts();
  }

  getProducts() {
    this.productService.getProductsNew(this.searchQuery, this.sorting, this.minPrice.toString(), this.maxPrice.toString(), this.categoriesFilter.toString(), this.brandsFilter.toString(), this.currentPage).subscribe(res => {
      this.results = res;
      this.results.data.forEach((item: Product) => {
        if (item.is_location_offer) {
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
    this.productService.getProductsNew(this.searchQuery, this.sorting, this.minPrice.toString(), this.maxPrice.toString(), this.categoriesFilter.toString(), this.brandsFilter.toString(), 0).subscribe(res => {
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

  selectParentWithSubcategories(parentCategory: any, event: any) {
    const isChecked = event.target.checked;

    this.updateCategoryFilter(parentCategory.id, isChecked);
    this.updateSubcategories(parentCategory, isChecked);

    this.filterProducts();
  }

  updateSubcategories(category: Category, isChecked: boolean) {
    category.sub_categories.forEach((subCat: Category) => {
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
    return this.categoriesFilter.includes(category.id);
  }

  filterProducts() {
    this.productService.getProductsNew(this.searchQuery, this.sorting, this.minPrice.toString(), this.maxPrice.toString(), this.categoriesFilter.toString(), this.brandsFilter.toString(), 0).subscribe(res => {
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

  filterByCategory(event: any) {
    this.resultState = 'filter_started';
    if (event.target.checked) {
      this.categoriesFilter.push(event.target.value);
    } else {
      this.categoriesFilter = this.categoriesFilter.filter(item => item !== event.target.value);
    }
    this.productService.getProductsNew(this.searchQuery, this.sorting, this.minPrice.toString(), this.maxPrice.toString(), this.categoriesFilter.toString(), this.brandsFilter.toString(), 0).subscribe(res => {
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
    this.productService.getProductsNew(this.searchQuery, this.sorting, this.minPrice.toString(), this.maxPrice.toString(), this.categoriesFilter.toString(), this.brandsFilter.toString(), 0).subscribe(res => {
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
    this.productService.getProductsNew(this.searchQuery, this.sorting, this.minPrice.toString(), this.maxPrice.toString(), this.categoriesFilter.toString(), this.brandsFilter.toString(), 0).subscribe(res => {
      this.results = res;
    });
  }

  changeSorting(event: any) {
    this.sorting = event.target.value;

    this.resultState = 'sorting_started';
    this.productService.getProductsNew(this.searchQuery, this.sorting, this.minPrice.toString(), this.maxPrice.toString(), this.categoriesFilter.toString(), this.brandsFilter.toString(), 0).subscribe(res => {
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
  }

}
