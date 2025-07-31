import {Component, inject, OnInit} from '@angular/core';
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from "@angular/forms";
import {Product} from "../../models/product";
import {ActivatedRoute, RouterLink} from "@angular/router";
import {first} from "rxjs/operators";
import countriesList from "../../../assets/countries.json";
import {UserService} from "../../_services/user.service";
import {NgClass} from "@angular/common";

@Component({
  selector: 'app-users-add-edit',
  templateUrl: './users-add-edit.component.html',
  imports: [
    ReactiveFormsModule,
    NgClass,
    RouterLink
  ],
  styleUrls: []
})
export class UsersAddEditComponent implements OnInit {
  private readonly formBuilder = inject(FormBuilder);
  private readonly route = inject(ActivatedRoute);
  private readonly userService = inject(UserService);

  form: FormGroup;
  products!: Product[];
  id: string;
  countries = countriesList;
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
      first_name: ['', [Validators.required]],
      last_name: ['', [Validators.required]],
      dob: ['', [Validators.required]],
      address: ['', [Validators.required]],
      city: ['', [Validators.required]],
      state: ['', []],
      country: ['', [Validators.required]],
      postcode: ['', []],
      phone: ['', []],
      enabled: ['', []],
      email: ['', [Validators.required, Validators.pattern("^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$")]],
      password: ['', []],
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
          this.hideAlert = true;
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
          this.hideAlert = true;
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
