import {Component, OnInit} from '@angular/core';
import {AbstractControl, FormBuilder, FormGroup} from "@angular/forms";
import {CustomerAccountService} from "../../shared/customer-account.service";
import {TokenStorageService} from "../../_services/token-storage.service";
import {User} from "../../models/user.model";
import {BrowserDetectorService} from "../../_services/browser-detector.service";

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {
  form: FormGroup | any;
  submitted = false;
  error: string | undefined;

  isLoggedIn = false;
  isLoginFailed = false;
  roles: string[] = [];

  constructor(private formBuilder: FormBuilder,
              private accountService: CustomerAccountService,
              private tokenStorage: TokenStorageService,
              public browserDetect: BrowserDetectorService) {
  }

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
