import {Component, inject, OnInit} from '@angular/core';
import {first} from "rxjs/operators";
import {CategoryService} from "../../_services/category.service";
import {Category} from "../../models/category";
import {ToastService} from "../../_services/toast.service";
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from "@angular/forms";
import {RouterLink} from "@angular/router";

@Component({
  selector: 'app-list',
  templateUrl: './categories-list.component.html',
  imports: [
    ReactiveFormsModule,
    RouterLink
  ],
  styleUrls: []
})
export class CategoriesListComponent implements OnInit {
  private readonly categoryService = inject(CategoryService);
  private readonly toastService = inject(ToastService);
  private readonly formBuilder = inject(FormBuilder);

  categories!: Category[];
  searchForm: FormGroup | any;

  ngOnInit(): void {
    this.getCategories();

    this.searchForm = this.formBuilder.group(
      {
        query: ['', [Validators.required]]
      });
  }

  search() {
    let query = this.searchForm.controls['query'].value;
    this.categoryService.searchCategories(query)
      .pipe(first())
      .subscribe((categories) => this.categories = categories);
  }

  reset() {
    this.getCategories();
  }

  deleteCategory(id: number) {
    this.categoryService.delete(id)
      .pipe(first())
      .subscribe({
        next: () => {
          this.toastService.show('Category deleted.', {classname: 'bg-success text-light'});
          this.getCategories();
        }, error: (err) => {
          this.toastService.show(err.message, {classname: 'bg-warning text-dark'})
        }
      });
  }

  getCategories() {
    this.categoryService.getCategories()
      .pipe(first())
      .subscribe((categories) => this.categories = categories);
  }
}
