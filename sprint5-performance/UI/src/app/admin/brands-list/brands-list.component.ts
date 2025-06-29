import {Component, inject, OnInit} from '@angular/core';
import {Brand} from "../../models/brand";
import {BrandService} from "../../_services/brand.service";
import {first} from 'rxjs/operators';
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from "@angular/forms";
import {ToastrService} from "ngx-toastr";
import {RenderDelayDirective} from "../../render-delay-directive.directive";
import {RouterLink} from "@angular/router";

@Component({
  selector: 'app-list',
  templateUrl: './brands-list.component.html',
  imports: [
    ReactiveFormsModule,
    RenderDelayDirective,
    RouterLink
  ],
  styleUrls: []
})
export class BrandsListComponent implements OnInit {
  private readonly brandService = inject(BrandService);
  private readonly formBuilder = inject(FormBuilder);
  private readonly toastr = inject(ToastrService);

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

  deleteBrand(id: string) {
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
