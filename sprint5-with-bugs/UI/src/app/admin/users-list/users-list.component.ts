import {Component, inject, OnInit} from '@angular/core';
import {ToastService} from "../../_services/toast.service";
import {first} from "rxjs/operators";
import {UserService} from "../../_services/user.service";
import {User} from "../../models/user.model";
import {Pagination} from "../../models/pagination";
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from "@angular/forms";
import {RouterLink} from "@angular/router";
import {NgxPaginationModule} from "ngx-pagination";

@Component({
  selector: 'app-users-list',
  templateUrl: './users-list.component.html',
  imports: [
    ReactiveFormsModule,
    RouterLink,
    NgxPaginationModule
  ],
  styleUrls: []
})
export class UsersListComponent implements OnInit {
  private readonly userService = inject(UserService);
  private readonly toastService = inject(ToastService);
  private readonly formBuilder = inject(FormBuilder);

  p: number = 1;
  results: Pagination<User>;
  searchForm: FormGroup | any;

  ngOnInit(): void {
    this.getUsers();

    this.searchForm = this.formBuilder.group(
      {
        query: ['', [Validators.required]]
      });
  }

  search() {
    let query = this.searchForm.controls['query'].value;
    this.userService.searchUsers(0, query)
      .pipe(first())
      .subscribe((users) => this.results = users);
  }

  reset() {
    this.p  = 0;
    this.getUsers();
  }

  deleteUser(id: number | any) {
    this.userService.delete(id)
      .pipe(first())
      .subscribe({
        next: () => {
          this.toastService.show('User deleted.', {classname: 'bg-success text-light'});
          this.getUsers();
        }, error: (err) => {
          this.toastService.show(err.message, {classname: 'bg-warning text-dark'})
        }
      });
  }

  getUsers() {
    this.userService.getUsers(this.p)
      .pipe(first())
      .subscribe((users) => this.results = users);
  }

  handlePageChange(event: number): void {
    this.p = event;
    this.getUsers();
  }

}
