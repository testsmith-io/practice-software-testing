import {Component, inject, OnInit} from '@angular/core';
import {CustomerAccountService} from "../../shared/customer-account.service";
import {AbstractControl, FormBuilder, FormGroup, ReactiveFormsModule, Validators} from "@angular/forms";
import {TokenStorageService} from "../../_services/token-storage.service";
import {HttpClient} from "@angular/common/http";
import {environment} from "../../../environments/environment";
import {NgClass} from "@angular/common";
import {RouterLink} from "@angular/router";
import {ArchwizardModule} from "@y3krulez/angular-archwizard";
import {TranslocoDirective} from "@jsverse/transloco";

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  imports: [
    ReactiveFormsModule,
    NgClass,
    RouterLink,
    ArchwizardModule,
    ArchwizardModule,
    TranslocoDirective
  ],
  styleUrls: []
})
export class LoginComponent implements OnInit {
  private readonly formBuilder = inject(FormBuilder);
  private readonly tokenStorage = inject(TokenStorageService);
  private readonly accountService = inject(CustomerAccountService);
  private readonly http = inject(HttpClient);

  isLoggedIn: boolean = false;
  cusForm: FormGroup | any;
  cusSubmitted = false;
  customerError: string | undefined;
  isLoginFailed = false;
  showTotpInput: boolean = false;
  accessToken: string = '';
  roles: string[] = [];
  customer: any;
  canExitStep2 = true;
  apiURL = environment.apiUrl;

  ngOnInit(): void {
    this.getCustomerInfo();
    this.isLoggedIn = this.accountService.isLoggedIn();

    this.cusForm = this.formBuilder.group(
      {
        email: ['', [Validators.required, Validators.email]],
        password: ['', [Validators.required,
          Validators.minLength(6),
          Validators.maxLength(40)]],
        totp: [''],
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

  onSubmit(): void {
    this.cusSubmitted = true;

    if (this.cusForm.invalid) {
      return;
    }

    const payload = {
      email: this.cusForm.value.email,
      password: this.cusForm.value.password,
    };

    this.accountService.login(payload).subscribe({
      next: (res) => {
        if (res.requires_totp) {
          // Step 1 successful: TOTP required
          this.showTotpInput = true;
          this.customerError = null;
          this.accessToken = res.access_token;
        } else {
          // Regular login
          this.handleSuccessfulLogin(res.access_token);
        }
      },
      error: (err) => {
        this.handleLoginError(err);
      },
    });
  }

  handleSuccessfulLogin(token: string): void {
    this.tokenStorage.saveToken(token);

    this.getCustomerInfo();
    this.isLoginFailed = false;
    this.isLoggedIn = true;
    this.accountService.authSub.next('changed');
    this.roles = this.accountService.getRole();
  }

  handleLoginError(err: any): void {
    if (err.error === 'Unauthorized') {
      this.customerError = 'Invalid email or password';
    } else {
      console.log(err);
      this.customerError = err.error || 'Login failed';
    }
    this.isLoginFailed = true;
  }

  handleLoginTOTPError(err: any): void {
    if (err.error === 'Unauthorized') {
      this.customerError = 'Invalid TOTP';
    } else {
      console.log(err);
      this.customerError = err.error.error || 'Login failed';
    }
    this.isLoginFailed = true;
  }


  private getCustomerInfo() {
    this.customer = this.accountService.getDetails().subscribe(res => {
      this.customer = res;
    });
  }

  verifyTotp(): void {
    if (!this.cusForm.value.totp) {
      this.customerError = 'TOTP code is required';
      this.isLoginFailed = true;
      return;
    }

    const payload = {
      totp: this.cusForm.value.totp,
      access_token: this.accessToken, // Send the access token for TOTP verification
    };

    this.http.post(this.apiURL +'/users/login', payload).subscribe({
      next: (res) => {
        // Step 2 successful: Complete login
        // @ts-ignore
        this.handleSuccessfulLogin(res.access_token);
      },
      error: (err) => {
        this.handleLoginTOTPError(err);
      },
    });
  }
}
