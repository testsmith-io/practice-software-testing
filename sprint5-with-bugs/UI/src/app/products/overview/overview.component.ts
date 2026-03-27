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
import {NgxSliderModule, Options} from "@angular-slider/ngx-slider";
import {NgxPaginationModule} from "ngx-pagination";
import {RouterLink} from "@angular/router";
import {NgClass, NgTemplateOutlet} from "@angular/common";

@Component({
  selector: 'app-overview',
  templateUrl: './overview.component.html',
  imports: [
    NgxSliderModule,
    ReactiveFormsModule,
    NgxPaginationModule,
    RouterLink,
    NgClass,
    NgTemplateOutlet
  ],
  styleUrls: ['./overview.component.css']
})
export class OverviewComponent implements OnInit {
  private readonly productService = inject(ProductService);
  private readonly formBuilder = inject(FormBuilder);
  private readonly brandService = inject(BrandService);
  private readonly categoryService = inject(CategoryService);
  public readonly browserDetect = inject(BrowserDetectorService);

  @ViewChildren("checkboxes") checkboxes: QueryList<ElementRef>;

  search: FormGroup | any;
  resultState: string = '';
  p: number = 1;
  results: Pagination<Product>;
  brands: Brand[];
  categories: any;
  private brandsFilter: Array<number> = [];
  private categoriesFilter: Array<number> = [];
  private sorting: string = '';
  searchQuery: string;
  minPrice: number = 1;
  maxPrice: number = 100;
  options: Options = {
    floor: 0,
    ceil: 200
  };
  protected readonly Math = Math;

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

  getProducts() {
    this.productService.getProductsNew(this.searchQuery, this.sorting, this.minPrice.toString(), this.maxPrice.toString(), this.categoriesFilter.toString(), this.brandsFilter.toString(), this.p).subscribe(res => {
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
      this.p = 1;
      this.results = res;
      this.results.data.forEach((item: Product) => {
        if (item.is_location_offer) {
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
    this.productService.getProductsNew(this.searchQuery, this.sorting, this.minPrice.toString(), this.maxPrice.toString(), this.categoriesFilter.toString(), this.brandsFilter.toString(), 0).subscribe(res => {
      this.resultState = 'filter_completed';
      this.p = 1;
      this.results = res;
      this.results.data.forEach((item: Product) => {
        if (item.is_location_offer) {
          item.discount_price = DiscountUtil.calculateDiscount(item.price);
        }
      })
    });
  }

  handlePageChange(event: number): void {
    this.p = event;
    this.getProducts();
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
