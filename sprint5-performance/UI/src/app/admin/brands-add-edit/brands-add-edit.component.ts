import {Component, inject, OnInit} from '@angular/core';
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from "@angular/forms";
import {ActivatedRoute, RouterLink} from "@angular/router";
import {BrandService} from "../../_services/brand.service";
import {first} from "rxjs/operators";
import {NgClass} from "@angular/common";

@Component({
  selector: 'app-brands-add-edit',
  templateUrl: './brands-add-edit.component.html',
  imports: [
    ReactiveFormsModule,
    NgClass,
    RouterLink
  ],
  styleUrls: []
})
export class BrandsAddEditComponent implements OnInit {
  private readonly formBuilder = inject(FormBuilder);
  private readonly route = inject(ActivatedRoute);
  private readonly brandService = inject(BrandService);

  form: FormGroup;
  id: string;
  isAddMode: boolean;
  submitted: boolean = false;
  isUpdated: boolean = false;
  hideAlert: boolean = false;
  error: string;

  ngOnInit(): void {
    this.id = this.route.snapshot.params['id'];
    this.isAddMode = !this.id;

    this.form = this.formBuilder.group({
      id: ['', []],
      name: ['', [Validators.required]],
      slug: ['', [Validators.required, Validators.pattern('^[a-z0-9-]+$')]]
    });

    if (!this.isAddMode) {
      this.brandService.getById(this.id)
        .pipe(first())
        .subscribe(x => this.form.patchValue(x));
    }
  }

  get f() {
    return this.form.controls;
  }

  onSubmit() {
    this.submitted = true;

    this.isUpdated = false;
    this.hideAlert = false;
    this.error = '';

    if (this.form.invalid) {
      return;
    }

    if (this.isAddMode) {
      this.createBrand();
    } else {
      this.updateBrand();
    }
  }

  private reset() {
    for (let name in this.form.controls) {
      this.form.controls[name].setValue('');
      this.form.controls[name].setErrors(null);
    }
  }

  private createBrand() {
    this.brandService.create(this.form.value)
      .pipe(first())
      .subscribe({
        next: () => {
          this.isUpdated = true;
          this.reset();
        }, error: (err) => {
          this.error = Object.values(err).join('\r\n');
        }, complete: () => {
          this.hideAlert = false;
        }
      });
  }

  private updateBrand() {
    this.brandService.update(this.id, this.form.value)
      .pipe(first())
      .subscribe({
        next: () => {
          this.isUpdated = true;
        }, error: (err) => {
          this.error = Object.values(err).join('\r\n');
        }, complete: () => {
          this.hideAlert = false;
        }
      });
  }

  fadeOutMessage(): any {
    setTimeout(() => {
      this.hideAlert = true;
    }, 3000);
  }

}
