import {Component, inject, OnInit} from '@angular/core';
import {AbstractControl, FormBuilder, FormGroup, ReactiveFormsModule, Validators} from "@angular/forms";
import {CustomerAccountService} from "../../shared/customer-account.service";
import countriesList from '../../../assets/countries.json';
import {User} from "../../models/user.model";
import {PasswordValidators} from "../../_helpers/password.validators";
import {NgClass} from "@angular/common";
import {PasswordInputComponent} from "../../shared/password-input/password-input.component";
import {TranslocoDirective} from "@jsverse/transloco";
import { DateValidators } from 'src/app/shared/validators/date.validators';

@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
    imports: [
        ReactiveFormsModule,
        NgClass,
        PasswordInputComponent,
        TranslocoDirective,
        ReactiveFormsModule
    ],
  styleUrls: ['./register.component.css']
})
export class RegisterComponent implements OnInit {
  private formBuilder = inject(FormBuilder);
  private accountService = inject(CustomerAccountService);

  register: FormGroup | any;
  submitted: boolean;

  countries = countriesList;
  error: string;

  passwordStrengthIndicator: string;

  ngOnInit(): void {
    this.register = this.formBuilder.group(
      {
        first_name: ['', [Validators.required]],
        last_name: ['', [Validators.required]],
        dob: ['', [Validators.required, DateValidators.isoDate]],
        street: ['', [Validators.required]],
        city: ['', [Validators.required]],
        state: ['', [Validators.required]],
        country: ['', [Validators.required]],
        postal_code: ['', [Validators.required]],
        phone: ['', [Validators.required, Validators.pattern(/^[0-9]\d*$/)]],
        email: ['', [Validators.required, Validators.pattern("^(?=.{1,256}$)[a-zA-Z0-9._%+-]{1,64}@[a-zA-Z0-9.-]{1,255}$")]],
        password: ['', [Validators.required,
          PasswordValidators.minLength(8),
          PasswordValidators.mixedCase(),
          PasswordValidators.hasNumber(),
          PasswordValidators.hasSymbol()
        ]],
      }
    );
  }

  getStrengthWidth(passwordStrength: string): string {
    switch(passwordStrength) {
      case 'Weak':
        return '20%';
      case 'Moderate':
        return '40%';
      case 'Strong':
        return '60%';
      case 'Very Strong':
        return '80%';
      case 'Excellent':
        return '100%'
      default:
        return '0%';
    }
  }

  passwordStrength(password: string): string {
    let strength = 0;
    if (password.length >= 8) strength += 1;
    if (/[a-z]/.test(password)) strength += 1;
    if (/[A-Z]/.test(password)) strength += 1;
    if (/\d/.test(password)) strength += 1;
    if (/[!\"#$%&'()*+,-./:;<=>?@[\\\]^_`{|}~]/.test(password)) strength += 1;

    switch (strength) {
      case 1: return 'Weak';
      case 2: return 'Moderate';
      case 3: return 'Strong';
      case 4: return 'Very Strong';
      case 5: return 'Excellent'
      default: return 'Invalid';
    }
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
      phone: this.register.value.phone,
      email: this.register.value.email,
      password: this.register.value.password,
      address: {
        street: this.register.value.street,
        city: this.register.value.city,
        state: this.register.value.state,
        country: this.register.value.country,
        postal_code: this.register.value.postal_code
      }
    };


    this.accountService.register(payload).subscribe({
      next: () => {
        this.accountService.redirectToLogin();
      }, error: (err) => {
        if (err.error === 'Duplicate Entry') {
          this.error = 'Email is already in use.';
        } else {
          this.error = Object.values(err)
            .map((fieldErrors: any) => fieldErrors.join('\n'))
            .join('\n');
        }
      }
    });
  }
}
