import {Component, inject, OnInit} from '@angular/core';
import {AbstractControl, FormBuilder, FormGroup, ReactiveFormsModule} from "@angular/forms";
import {CustomerAccountService} from "../../shared/customer-account.service";
import {TokenStorageService} from "../../_services/token-storage.service";
import {User} from "../../models/user.model";
import {BrowserDetectorService} from "../../_services/browser-detector.service";
import {NgClass, NgStyle} from "@angular/common";
import {PasswordInputComponent} from "../../shared/password-input/password-input.component";
import {RouterLink} from "@angular/router";

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  imports: [
    ReactiveFormsModule,
    NgClass,
    NgStyle,
    PasswordInputComponent,
    RouterLink
  ],
  styleUrls: []
})
export class LoginComponent implements OnInit {
  private readonly formBuilder = inject(FormBuilder);
  private readonly accountService = inject(CustomerAccountService);
  private readonly tokenStorage = inject(TokenStorageService);
  public readonly browserDetect = inject(BrowserDetectorService);

  form: FormGroup | any;
  submitted = false;
  error: string | undefined;

  isLoggedIn = false;
  isLoginFailed = false;
  roles: string[] = [];

  ngOnInit(): void {
    if (this.tokenStorage.getToken()) {
      this.isLoggedIn = true;
    }

    this.form = this.formBuilder.group(
      {
        email: ['', []],
        password: ['', []],
      }
    );
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

    const payload: User = {
      email: this.form.value.email,
      password: this.form.value.password
    };

    this.accountService.login(payload).subscribe({
      next: (res) => {
        this.tokenStorage.saveToken(res.access_token);

        this.isLoginFailed = false;
        this.isLoggedIn = true;
        this.accountService.authSub.next('changed');
        if (this.accountService.getRole() === 'user') {
          this.accountService.redirectToAccount();
        } else if (this.accountService.getRole() === 'admin') {
          this.accountService.redirectToDashboard();
        }
      }, error: (err) => {
        if (err.error === 'Unauthorized') {
          this.error = 'Invalid email or password';
          this.isLoginFailed = true;
        } else {
          this.error = err.error;
          this.isLoginFailed = true;
        }
      },
    });

  }

}
