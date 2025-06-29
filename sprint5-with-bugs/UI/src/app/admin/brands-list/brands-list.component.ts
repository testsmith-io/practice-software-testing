import {Component, inject, OnInit} from '@angular/core';
import {Brand} from "../../models/brand";
import {BrandService} from "../../_services/brand.service";
import {first} from 'rxjs/operators';
import {ToastService} from "../../_services/toast.service";
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from "@angular/forms";
import {RouterLink} from "@angular/router";

@Component({
  selector: 'app-list',
  templateUrl: './brands-list.component.html',
  imports: [
    ReactiveFormsModule,
    RouterLink
  ],
  styleUrls: []
})
export class BrandsListComponent implements OnInit {
  private readonly brandService = inject(BrandService);
  private readonly toastService = inject(ToastService);
  private readonly formBuilder = inject(FormBuilder);

  brands!: Brand[];
  searchForm: FormGroup | any;

  ngOnInit(): void {
    this.getBrands();

    this.searchForm = this.formBuilder.group(
      {
        query: ['', [Validators.required]]
      });
  }

  search() {
    let query = this.searchForm.controls['query'].value;
    this.brandService.searchBrands(query)
      .pipe(first())
      .subscribe((brands) => this.brands = brands);
  }

  reset() {
    this.getBrands();
  }

  deleteBrand(id: number) {
    this.brandService.delete(id)
      .pipe(first())
      .subscribe({
        next: () => {
          this.toastService.show('Brand deleted.', {classname: 'bg-success text-light'});
          this.getBrands();
        }, error: (err) => {
          this.toastService.show(err.message, {classname: 'bg-warning text-dark'})
        }
      });
  }

  getBrands() {
    this.brandService.getBrands()
      .pipe(first())
      .subscribe((brands) => this.brands = brands);
  }
}
