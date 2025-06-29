import {Component, inject, OnInit} from '@angular/core';
import {AbstractControl, FormBuilder, FormGroup, ReactiveFormsModule, Validators} from '@angular/forms';
import {CustomerAccountService} from '../../shared/customer-account.service';
import {TokenStorageService} from '../../_services/token-storage.service';
import {ActivatedRoute, RouterLink} from '@angular/router';
import {TotpAuthService} from '../../_services/totp-auth.service';
import {BrowserService} from '../../_services/browser.service';
import {environment} from '../../../environments/environment';
import {TranslocoDirective} from "@jsverse/transloco";
import {NgClass} from "@angular/common";
import {PasswordInputComponent} from "../../shared/password-input/password-input.component";


@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  imports: [
    ReactiveFormsModule,
    TranslocoDirective,
    NgClass,
    PasswordInputComponent,
    RouterLink
  ],
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {
  private readonly formBuilder = inject(FormBuilder);
  private readonly accountService = inject(CustomerAccountService);
  private readonly tokenStorage = inject(TokenStorageService);
  private readonly activatedRoute = inject(ActivatedRoute);
  private readonly totpAuthService = inject(TotpAuthService);
  private readonly browser = inject(BrowserService);

  form: FormGroup;
  submitted = false;
  error?: string;
  isLoggedIn = false;
  isLoginFailed = false;
  showTotpInput = false;
  accessToken = '';
  protected readonly environment = environment;

  ngOnInit(): void {
    this.activatedRoute.params.subscribe(params => {
      const socialid = params['socialid'];
      if (socialid) {
        this.tokenStorage.saveToken(socialid);
        this.accountService.authSub.next('changed');
        this.accountService.redirectToAccount();
      }
    });

    if (this.tokenStorage.getToken()) {
      this.isLoggedIn = true;
    }

    this.form = this.formBuilder.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(3), Validators.maxLength(40)]],
      totp: ['']
    });
  }

  get email(): AbstractControl {
    return this.form.get('email');
  }

  get password(): AbstractControl {
    return this.form.get('password');
  }

  get cf(): { [key: string]: AbstractControl } {
    return this.form.controls;
  }

  onSubmit(): void {
    this.submitted = true;
    if (this.form.invalid) return;

    const payload = {
      email: this.form.value.email,
      password: this.form.value.password,
    };

    this.accountService.login(payload).subscribe({
      next: (res) => {
        if (res.requires_totp) {
          this.showTotpInput = true;
          this.error = null;
          this.accessToken = res.access_token;
        } else {
          this.handleSuccessfulLogin(res.access_token);
        }
      },
      error: (err) => this.handleLoginError(err),
    });
  }

  verifyTotp(): void {
    if (!this.form.value.totp) {
      this.error = 'TOTP code is required';
      this.isLoginFailed = true;
      return;
    }

    this.totpAuthService.verifyTotp(this.form.value.totp, this.accessToken).subscribe({
      next: (res: { access_token: string; }) => this.handleSuccessfulLogin(res.access_token),
      error: (err: any) => this.handleLoginTOTPError(err)
    });
  }

  handleSuccessfulLogin(token: string): void {
    this.tokenStorage.saveToken(token);
    this.isLoginFailed = false;
    this.isLoggedIn = true;
    this.accountService.authSub.next('changed');

    const role = this.accountService.getRole();
    if (role === 'user') {
      this.accountService.redirectToAccount();
    } else if (role === 'admin') {
      this.accountService.redirectToDashboard();
    }
  }

  handleLoginError(err: any): void {
    this.error = err.error === 'Unauthorized'
      ? 'Invalid email or password'
      : err.error || 'Login failed';
    this.isLoginFailed = true;
  }

  handleLoginTOTPError(err: any): void {
    this.error = err.error === 'Unauthorized'
      ? 'Invalid TOTP'
      : err.error?.error || 'Login failed';
    this.isLoginFailed = true;
  }

  socialLogin(provider: string): void {
    this.browser.open(
      `https://api.practicesoftwaretesting.com/auth/social-login?provider=${provider}`,
      '',
      'height=500,width=400'
    );
  }
}
