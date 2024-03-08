import {Component, OnInit} from '@angular/core';
import {CustomerAccountService} from "../../shared/customer-account.service";
import {AbstractControl, FormBuilder, FormGroup, Validators} from "@angular/forms";
import {TokenStorageService} from "../../_services/token-storage.service";

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrl: './login.component.css'
})
export class LoginComponent implements OnInit {

  isLoggedIn: boolean = false;
  cusForm: FormGroup | any;
  cusSubmitted = false;
  customerError: string | undefined;
  isLoginFailed = false;
  roles: string[] = [];
  customer: any;
  canExitStep2 = true;

  constructor(private formBuilder: FormBuilder,
              private tokenStorage: TokenStorageService,
              private customerAccountService: CustomerAccountService) {
  }
  ngOnInit(): void {
    this.getCustomerInfo();
    this.isLoggedIn = this.customerAccountService.isLoggedIn();

    this.cusForm = this.formBuilder.group(
      {
        email: ['', [Validators.required, Validators.email]],
        password: ['', [Validators.required,
          Validators.minLength(6),
          Validators.maxLength(40)]],
      }
    );
  }

  get cus_email() {
    return this.cusForm.get('email');
  }

  get cus_password() {
    return this.cusForm.get('password');
  }

  get cf(): { [key: string]: AbstractControl } {
    return this.cusForm.controls;
  }
  onCusSubmit(): void {
    this.cusSubmitted = true;

    if (this.cusForm.invalid) {
      return;
    }

    const payload = {
      'email': this.cusForm.value.email,
      'password': this.cusForm.value.password
    };

    this.customerAccountService.login(payload).pipe().subscribe(res => {
      this.tokenStorage.saveToken(res.access_token);

      this.getCustomerInfo();
      this.isLoginFailed = false;
      this.isLoggedIn = true;
      this.customerAccountService.authSub.next('changed');
      this.roles = this.customerAccountService.getRole();
    }, err => {
      if (err.error === 'Unauthorized') {
        this.customerError = 'Invalid email or password';
        this.isLoginFailed = true;
      } else {
        this.customerError = err.error;
        this.isLoginFailed = true;
      }
    });

  }

  private getCustomerInfo() {
    this.customer = this.customerAccountService.getDetails().subscribe(res => {
      this.customer = res;
    });
  }

}
