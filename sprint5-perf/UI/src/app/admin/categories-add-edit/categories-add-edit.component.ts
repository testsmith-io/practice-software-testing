import {Component, OnInit} from '@angular/core';
import {FormBuilder, FormGroup, Validators} from "@angular/forms";
import {ActivatedRoute, Router} from "@angular/router";
import {first} from "rxjs/operators";
import {CategoryService} from "../../_services/category.service";
import {Category} from "../../models/category";

@Component({
  selector: 'app-categories-add-edit',
  templateUrl: './categories-add-edit.component.html',
  styleUrls: ['./categories-add-edit.component.css']
})
export class CategoriesAddEditComponent implements OnInit {
  form: FormGroup;
  categories!: Category[];
  id: string;
  isAddMode: boolean;
  submitted: boolean = false;
  isUpdated: boolean = false;
  hideAlert: boolean = false;
  error: string;

  constructor(
    private formBuilder: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private categoryService: CategoryService
  ) {
  }

  ngOnInit(): void {
    this.id = this.route.snapshot.params['id'];
    this.isAddMode = !this.id;

    this.form = this.formBuilder.group({
      id: ['', []],
      parent_id: ['', []],
      name: ['', [Validators.required]],
      slug: ['', [Validators.required, Validators.pattern('^[a-z0-9-]+$')]]
    });

    this.categoryService.getCategories()
      .pipe(first())
      .subscribe((categories) => this.categories = categories);

    if (!this.isAddMode) {
      this.categoryService.getById(this.id)
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

    if (this.form.invalid) {
      return;
    }

    if(this.form.controls['parent_id'].value === ''){
      this.form.controls['parent_id'].setValue(null)
    }

    if (this.isAddMode) {
      this.createBrand();
      this.reset();
    } else {
      this.updateBrand();
    }
  }

  private createBrand() {
    this.categoryService.create(this.form.value)
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

  private updateBrand() {
    this.categoryService.update(this.id, this.form.value)
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

  private reset() {
    for (let name in this.form.controls) {
      this.form.controls[name].setValue('');
      this.form.controls[name].setErrors(null);
    }
  }
}
