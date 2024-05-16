import {Component, OnInit} from '@angular/core';
import {Brand} from "../../models/brand";
import {BrandService} from "../../_services/brand.service";
import {first} from 'rxjs/operators';
import {FormBuilder, FormGroup, Validators} from "@angular/forms";
import {ToastrService} from "ngx-toastr";

@Component({
  selector: 'app-list',
  templateUrl: './brands-list.component.html',
  styleUrls: ['./brands-list.component.css']
})
export class BrandsListComponent implements OnInit {
  brands!: Brand[];
  searchForm: FormGroup | any;

  constructor(private brandService: BrandService,
              private formBuilder: FormBuilder,
              private toastr: ToastrService) {
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
          this.toastr.success('Brand deleted.', null, {progressBar: true});
          this.getBrands();
        }, error: (err) => {
          this.toastr.error(err.message, null, {progressBar: true})
        }
      });
  }

  getBrands() {
    this.brandService.getBrands()
      .pipe(first())
      .subscribe((brands) => this.brands = brands);
  }
}
