import {Component, OnInit} from '@angular/core';
import {FormBuilder, FormGroup, Validators} from "@angular/forms";
import {Product} from "../../models/product";
import {ActivatedRoute, Router} from "@angular/router";
import {first} from "rxjs/operators";
import countriesList from "../../../assets/countries.json";
import {UserService} from "../../_services/user.service";

@Component({
  selector: 'app-users-add-edit',
  templateUrl: './users-add-edit.component.html',
  styleUrls: ['./users-add-edit.component.css']
})
export class UsersAddEditComponent implements OnInit {

  form: FormGroup;
  products!: Product[];
  id: string;
  countries = countriesList;
  isAddMode: boolean;
  submitted: boolean = false;
  isUpdated: boolean = false;
  hideAlert: boolean = false;
  error: string;

  constructor(
    private formBuilder: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private userService: UserService) {
  }

  ngOnInit(): void {
    this.id = this.route.snapshot.params['id'];
    this.isAddMode = !this.id;

    this.form = this.formBuilder.group({
      id: ['', []],
      first_name: ['', [Validators.required]],
      last_name: ['', [Validators.required]],
      dob: ['', [Validators.required]],
      address: ['', [Validators.required]],
      city: ['', [Validators.required]],
      state: ['', []],
      country: ['', [Validators.required]],
      postcode: ['', []],
      phone: ['', []],
      email: ['', [Validators.required, Validators.pattern("^(?=.{1,256}$)[a-zA-Z0-9._%+-]{1,64}@[a-zA-Z0-9.-]{1,255}$")]],
      failed_login_attempts: ['', []],
      enabled: ['', []],
      password: ['', [Validators.minLength(6), Validators.maxLength(40)]],
    });

    if (!this.isAddMode) {
      this.userService.getById(this.id)
        .pipe(first())
        .subscribe(x => {
          this.form.patchValue(x)
        });
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
      this.createUser();
    } else {
      this.updateUser();
    }
  }

  private createUser() {
    this.userService.create(this.form.value)
      .pipe(first())
      .subscribe({
        next: () => {
          this.isUpdated = true;
        }, error: (err) => {
          this.error = Object.values(err).join('\r\n');
        }, complete: () => {
          this.hideAlert = false;
          this.reset();
        }
      });
  }

  private updateUser() {
    const formValue = { ...this.form.value }; // Create a copy of the form value
    delete formValue.password; // Remove the password field from the form value object

    this.userService.update(this.id, formValue)
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
