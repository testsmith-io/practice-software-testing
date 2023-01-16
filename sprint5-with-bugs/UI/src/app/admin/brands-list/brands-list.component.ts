import {Component, OnInit} from '@angular/core';
import {Brand} from "../../models/brand";
import {BrandService} from "../../_services/brand.service";
import {first} from 'rxjs/operators';
import {ToastService} from "../../_services/toast.service";
import {FormBuilder, FormGroup, Validators} from "@angular/forms";

@Component({
  selector: 'app-list',
  templateUrl: './brands-list.component.html',
  styleUrls: ['./brands-list.component.css']
})
export class BrandsListComponent implements OnInit {
  brands!: Brand[];
  searchForm: FormGroup | any;

  constructor(private brandService: BrandService,
              private toastService: ToastService,
              private formBuilder: FormBuilder) {
  }

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
