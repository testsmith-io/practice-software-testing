import {Component, OnInit} from '@angular/core';
import {first} from "rxjs/operators";
import {CategoryService} from "../../_services/category.service";
import {Category} from "../../models/category";
import {FormBuilder, FormGroup, Validators} from "@angular/forms";
import {ToastrService} from "ngx-toastr";

@Component({
  selector: 'app-list',
  templateUrl: './categories-list.component.html',
  styleUrls: ['./categories-list.component.css']
})
export class CategoriesListComponent implements OnInit {
  categories!: Category[];
  searchForm: FormGroup | any;

  constructor(private categoryService: CategoryService,
              private toastr: ToastrService,
              private formBuilder: FormBuilder) {
  }

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
