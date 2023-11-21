import {Component, OnInit} from '@angular/core';
import {ToastService} from "../../_services/toast.service";
import {first} from "rxjs/operators";
import {UserService} from "../../_services/user.service";
import {User} from "../../models/user.model";
import {Pagination} from "../../models/pagination";
import {FormBuilder, FormGroup, Validators} from "@angular/forms";

@Component({
  selector: 'app-users-list',
  templateUrl: './users-list.component.html',
  styleUrls: ['./users-list.component.css']
})
export class UsersListComponent implements OnInit {
  currentPage: number = 1;
  results: Pagination<User>;
  searchForm: FormGroup | any;

  constructor(private userService: UserService,
              private toastService: ToastService,
              private formBuilder: FormBuilder) {
  }

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
    this.currentPage = 0;
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
    this.userService.getUsers(this.currentPage)
      .pipe(first())
      .subscribe((users) => this.results = users);
  }

  onPageChange(page: number) {
    // Handle page change here (e.g., fetch data for the selected page)
    this.currentPage = page;
    this.getUsers();
  }

}
