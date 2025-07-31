import {Component, inject, OnInit} from '@angular/core';
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
import {TranslocoDirective} from "@jsverse/transloco";
import {PaginationComponent} from "../../pagination/pagination.component";
import {NgClass, NgTemplateOutlet, TitleCasePipe} from "@angular/common";

@Component({
  selector: 'app-category',
  templateUrl: './category.component.html',
  imports: [
    FaIconComponent,
    TranslocoDirective,
    RouterLink,
    PaginationComponent,
    NgClass,
    TitleCasePipe,
    NgTemplateOutlet
  ],
  styleUrls: ['./category.component.css']
})
export class CategoryComponent implements OnInit {
  private readonly productService = inject(ProductService);
  private readonly formBuilder = inject(FormBuilder);
  private readonly route = inject(ActivatedRoute);
  private readonly brandService = inject(BrandService);
  private readonly categoryService = inject(CategoryService);
  public readonly browserDetect = inject(BrowserDetectorService);
  private readonly titleService = inject(Title);

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

  ngOnInit(): void {
    this.route.params.subscribe(params => {
      this.slug = params['name'];

      this.getProductsByCategory(this.slug);
      this.brandService.getBrands().subscribe(response => {
        this.brands = response;
      });

      this.categoryService.getSubCategoriesTreeBySlug(this.slug).subscribe(response => {
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
    this.productService.getProductsByCategoryAndBrand(this.categoriesFilter.toString(), this.brandsFilter.toString(), this.sorting, this.slug).subscribe(res => {
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
    this.productService.getProductsByCategoryAndBrand(this.categoriesFilter.toString(), this.brandsFilter.toString(), this.sorting, this.slug).subscribe(res => {
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
    this.productService.getProductsByCategoryAndBrand(this.categoriesFilter.toString(), this.brandsFilter.toString(), this.sorting, this.slug).subscribe(res => {
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

}
