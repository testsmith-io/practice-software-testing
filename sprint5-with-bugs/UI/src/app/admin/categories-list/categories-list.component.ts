import {Component, OnInit} from '@angular/core';
import {first} from "rxjs/operators";
import {CategoryService} from "../../_services/category.service";
import {Category} from "../../models/category";
import {ToastService} from "../../_services/toast.service";
import {FormBuilder, FormGroup, Validators} from "@angular/forms";

@Component({
  selector: 'app-list',
  templateUrl: './categories-list.component.html',
  styleUrls: ['./categories-list.component.css']
})
export class CategoriesListComponent implements OnInit {
  categories!: Category[];
  searchForm: FormGroup | any;

  constructor(private categoryService: CategoryService,
              private toastService: ToastService,
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
