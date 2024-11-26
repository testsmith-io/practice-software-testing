import {Component, OnInit} from '@angular/core';
import {AbstractControl, FormBuilder, FormGroup, Validators} from "@angular/forms";
import {CustomerAccountService} from "../../shared/customer-account.service";
import {TokenStorageService} from "../../_services/token-storage.service";
import {environment} from "../../../environments/environment";
import {ActivatedRoute} from "@angular/router";
import {HttpClient} from "@angular/common/http";

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {
  apiURL = environment.apiUrl;
  form: FormGroup | any;
  submitted = false;
  error: string | undefined;

  isLoggedIn = false;
  isLoginFailed = false;
  showTotpInput: boolean = false;
  accessToken: string = '';
  roles: string[] = [];
  protected readonly environment = environment;

  constructor(private formBuilder: FormBuilder,
              private accountService: CustomerAccountService,
              private tokenStorage: TokenStorageService,
              private activatedRoute: ActivatedRoute,
              private http: HttpClient) {
  }

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

    this.form = this.formBuilder.group(
      {
        email: ['', [Validators.required, Validators.email]],
        password: ['', [Validators.required,
          Validators.minLength(3),
          Validators.maxLength(40)]],
        totp: [''],
      }
    );
  }

  verifyTotp(): void {
    if (!this.form.value.totp) {
      this.error = 'TOTP code is required';
      this.isLoginFailed = true;
      return;
    }

    const payload = {
      totp: this.form.value.totp,
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

  get email() {
    return this.form.get('email');
  }

  get password() {
    return this.form.get('password');
  }

  get cf(): { [key: string]: AbstractControl } {
    return this.form.controls;
  }

  onSubmit(): void {
    this.submitted = true;

    if (this.form.invalid) {
      return;
    }

    const payload = {
      email: this.form.value.email,
      password: this.form.value.password,
    };

    this.accountService.login(payload).subscribe({
      next: (res) => {
        if (res.requires_totp) {
          // Step 1 successful: TOTP required
          this.showTotpInput = true;
          this.error = null;
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

    this.isLoginFailed = false;
    this.isLoggedIn = true;
    this.accountService.authSub.next('changed');

    if (this.accountService.getRole() === 'user') {
      this.accountService.redirectToAccount();
    } else if (this.accountService.getRole() === 'admin') {
      this.accountService.redirectToDashboard();
    }
  }

  handleLoginError(err: any): void {
    if (err.error === 'Unauthorized') {
      this.error = 'Invalid email or password';
    } else {
      console.log(err);
      this.error = err.error || 'Login failed';
    }
    this.isLoginFailed = true;
  }

  handleLoginTOTPError(err: any): void {
    if (err.error === 'Unauthorized') {
      this.error = 'Invalid TOTP';
    } else {
      console.log(err);
      this.error = err.error.error || 'Login failed';
    }
    this.isLoginFailed = true;
  }

  socialLogin(provider: string) {
    window.open('https://api.practicesoftwaretesting.com/auth/social-login?provider=' + provider, '', 'height=500,width=400');
  }

}
