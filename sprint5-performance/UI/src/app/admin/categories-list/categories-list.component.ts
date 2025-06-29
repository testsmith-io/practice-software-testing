import {Component, inject, OnInit} from '@angular/core';
import {first} from "rxjs/operators";
import {CategoryService} from "../../_services/category.service";
import {Category} from "../../models/category";
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from "@angular/forms";
import {ToastrService} from "ngx-toastr";
import {RenderDelayDirective} from "../../render-delay-directive.directive";
import {RouterLink} from "@angular/router";

@Component({
  selector: 'app-list',
  templateUrl: './categories-list.component.html',
  imports: [
    ReactiveFormsModule,
    RenderDelayDirective,
    RouterLink
  ],
  styleUrls: []
})
export class CategoriesListComponent implements OnInit {
  private readonly categoryService = inject(CategoryService);
  private readonly toastr = inject(ToastrService);
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
          this.toastr.success('Category deleted.', null, {progressBar: true});
          this.getCategories();
        }, error: (err) => {
          this.toastr.error(err.message, null, {progressBar: true})
        }
      });
  }

  getCategories() {
    this.categoryService.getCategories()
      .pipe(first())
      .subscribe((categories) => this.categories = categories);
  }
}
