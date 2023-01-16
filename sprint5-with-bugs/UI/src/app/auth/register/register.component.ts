import {Component, OnInit} from '@angular/core';
import {AbstractControl, FormBuilder, FormGroup, Validators} from "@angular/forms";
import {CustomerAccountService} from "../../shared/customer-account.service";
import countriesList from '../../../assets/countries.json';
import {User} from "../../models/user.model";

@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.css']
})
export class RegisterComponent implements OnInit {
  register: FormGroup | any;
  submitted: boolean;

  countries = countriesList;
  error: string;

  constructor(private formBuilder: FormBuilder, private accountService: CustomerAccountService) {
  }

  ngOnInit(): void {
    this.register = this.formBuilder.group(
      {
        first_name: ['', [Validators.required, Validators.pattern(/^[a-zA-Z]+$/)]],
        last_name: ['', [Validators.required, Validators.pattern(/^[a-zA-Z]+$/)]],
        dob: ['', [Validators.required]],
        address: ['', [Validators.required]],
        city: ['', [Validators.required]],
        state: ['', [Validators.required]],
        country: ['', [Validators.required]],
        postcode: ['', [Validators.required]],
        phone: ['', [Validators.required, Validators.pattern(/^[0-9]\d*$/)]],
        email: ['', [Validators.required, Validators.pattern("^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$")]],
        password: ['', [Validators.required,
          Validators.minLength(10),
          Validators.maxLength(40)]],
      }
    );
  }

  get f(): { [key: string]: AbstractControl } {
    return this.register.controls;
  }

  onSubmit() {
    this.submitted = true;

    let newDate = this.register.value.dob.split("-").reverse().join("-");

    if (this.register.invalid) {
      return;
    }

    const payload: User = {
      first_name: this.register.value.first_name,
      last_name: this.register.value.last_name,
      dob: this.register.value.dob,
      address: this.register.value.address,
      city: this.register.value.city,
      state: this.register.value.state,
      country: this.register.value.country,
      postcode: this.register.value.postcode,
      phone: this.register.value.phone,
      email: this.register.value.email,
      password: this.register.value.password
    };

    this.accountService.register(payload).subscribe({
      next: () => {
        this.accountService.redirectToLogin();
      }, error: (err) => {
        if (err.message === 'Duplicate Entry') {
          this.error = 'User already registered - Your password hint is: Name of your cat!';
        } else {
          this.error = Object.values(err).join('\r\n');
        }
      }
    });
  }
}
