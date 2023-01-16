import {Component, ElementRef, OnInit, QueryList, ViewChildren} from '@angular/core';
import {FormBuilder, FormGroup, Validators} from "@angular/forms";
import {Brand} from "../../models/brand";
import {BrandService} from "../../_services/brand.service";
import {CategoryService} from "../../_services/category.service";
import {Product} from "../../models/product";
import {Pagination} from "../../models/pagination";
import {ProductService} from "../../_services/product.service";
import {BrowserDetectorService} from "../../_services/browser-detector.service";

@Component({
  selector: 'app-overview',
  templateUrl: './overview.component.html',
  styleUrls: ['./overview.component.css']
})
export class OverviewComponent implements OnInit {

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

  constructor(private productService: ProductService,
              private formBuilder: FormBuilder,
              private brandService: BrandService,
              private categoryService: CategoryService,
              public browserDetect: BrowserDetectorService) {
  }

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
    this.productService.getProducts(this.p).subscribe(res => {
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
    this.productService.getProductsByCategoryAndBrand(this.categoriesFilter.toString(), this.brandsFilter.toString(), this.sorting).subscribe(res => {
      this.resultState = 'filter_completed';
      this.p = 1;
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
    this.productService.getProductsByCategoryAndBrand(this.categoriesFilter.toString(), this.brandsFilter.toString(), this.sorting).subscribe(res => {
      this.resultState = 'filter_completed';
      this.p = 1;
      this.results = res;
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
    this.productService.searchProducts(this.search.value.query).subscribe(res => {
      this.resultState = 'search_completed';
      this.results = res;
    });
    this.search.reset();
    this.uncheckAll();
  }

  changeSorting(event: any) {
    this.sorting = event.target.value;

    this.resultState = 'sorting_started';
    this.productService.getProductsByCategoryAndBrand(this.categoriesFilter.toString(), this.brandsFilter.toString(), this.sorting).subscribe(res => {
      this.results = res;
      this.resultState = 'sorting_completed';
    });
  }

  uncheckAll() {
    this.checkboxes.forEach((element) => {
      element.nativeElement.checked = false;
    });
  }
}
