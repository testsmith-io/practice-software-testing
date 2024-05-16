import {Component, OnInit} from '@angular/core';
import {first} from "rxjs/operators";
import {ProductService} from "../../_services/product.service";
import {Product} from "../../models/product";
import {Pagination} from "../../models/pagination";
import {FormBuilder, FormGroup, Validators} from "@angular/forms";
import {ToastrService} from "ngx-toastr";

@Component({
  selector: 'app-products-list',
  templateUrl: './products-list.component.html',
  styleUrls: ['./products-list.component.css']
})
export class ProductsListComponent implements OnInit {

  currentPage: number = 1;
  products!: Pagination<Product>;
  searchForm: FormGroup | any;

  constructor(private productService: ProductService,
              private toastr: ToastrService,
              private formBuilder: FormBuilder) {
  }

  ngOnInit(): void {
    this.getProducts();

    this.searchForm = this.formBuilder.group(
      {
        query: ['', [Validators.required]]
      });
  }

  search() {
    let query = this.searchForm.controls['query'].value;
    this.productService.searchProducts(query)
      .pipe(first())
      .subscribe((products) => this.products = products);
  }

  reset() {
    this.currentPage  = 0;
    this.getProducts();
  }

  deleteProduct(id: number) {
    this.productService.delete(id)
      .pipe(first())
      .subscribe({
        next: () => {
          this.toastr.success('Product deleted.', null, {progressBar: true});
          this.getProducts();
        }, error: (err) => {
          this.toastr.error(err.message, null, {progressBar: true})
        }
      });
  }

  getProducts() {
    this.productService.getProducts(this.currentPage)
      .pipe(first())
      .subscribe((products) => this.products = products);
  }

  onPageChange(page: number) {
    // Handle page change here (e.g., fetch data for the selected page)
    this.currentPage = page;
    this.getProducts();
  }

}
