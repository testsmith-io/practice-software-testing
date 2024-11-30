import {Component, OnInit} from '@angular/core';
import {CustomerAccountService} from "../../shared/customer-account.service";
import {first} from "rxjs/operators";
import {FormControl, FormGroup, Validators} from "@angular/forms";
import {PasswordValidators} from "../../_helpers/password.validators";
import {HttpClient} from "@angular/common/http";
import {environment} from "../../../environments/environment";
import { Profile } from 'src/app/models/profile';

@Component({
  selector: 'app-profile',
  templateUrl: './profile.component.html',
  styleUrls: ['./profile.component.css']
})
export class ProfileComponent implements OnInit {
  profile!: Profile;
  profileForm!: FormGroup;
  passwordForm!: FormGroup;
  totpForm!: FormGroup;
  isProfileUpdated: boolean = false;
  isPasswordUpdated: boolean = false;
  passwordError: string;
  profileError: string;
  hideProfileAlert: boolean = false;
  hidePasswordAlert: boolean = false;

  passwordStrengthIndicator: string;

  apiURL = environment.apiUrl;
  qrCodeUrl: string = '';
  secret: string = '';
  totpCode: string = '';
  errorMessage: string = '';
  successMessage: string = '';
  constructor(private customerAccountService: CustomerAccountService,
              private auth: CustomerAccountService,
              private http: HttpClient) {
  }

  ngOnInit(): void {
    this.customerAccountService.getDetails()
      .pipe(first())
      .subscribe((profile) => {
        this.profile = profile;
        this.profileForm.patchValue(profile);
      }, (error) => {
        if (error.status === 401 || error.status === 403) {
          window.localStorage.removeItem('TOKEN_KEY');
          window.location.href = '/auth/login';
        }
      });

    this.profileForm = new FormGroup({
      first_name: new FormControl('', [Validators.required]),
      last_name: new FormControl('', [Validators.required]),
      email: new FormControl('', [Validators.required, Validators.email]),
      phone: new FormControl('', [Validators.required]),
      address: new FormControl('', [Validators.required]),
      state: new FormControl('', [Validators.required]),
      country: new FormControl('', [Validators.required]),
      postcode: new FormControl('', [Validators.required]),
      city: new FormControl('', [Validators.required]),
    });

    this.passwordForm = new FormGroup({
      current_password: new FormControl('', [Validators.required]),
      new_password: new FormControl('', [Validators.required,
        PasswordValidators.minLength(8),
        PasswordValidators.mixedCase(),
        PasswordValidators.hasNumber(),
        PasswordValidators.hasSymbol()
      ]),
      new_password_confirmation: new FormControl('', [Validators.required, PasswordValidators.passwordsMatch()]),
    });

    this.totpForm = new FormGroup({
      totpCode: new FormControl('', [Validators.required])
    });

    this.getTotpSetup();
  }

  get f() {
    return this.profileForm.controls;
  }

  get p() {
    return this.passwordForm.controls;
  }

  updateProfile() {
    this.customerAccountService.update(this.profile.id, this.profileForm.value).subscribe({
      next: (res) => {
        if (res.success) {
          this.isProfileUpdated = true;
          this.hideProfileAlert = false;
        }
      }, error: (err) => {
        this.hideProfileAlert = false;
        if (err && typeof err === 'object') {
          const errorMessages = [];
          for (const field in err) {
            if (err.hasOwnProperty(field)) {
              errorMessages.push(...err[field]);
            }
          }
          this.profileError = errorMessages.join('\n\r');
        }
      }
    });
  }

  updatePassword() {
    this.customerAccountService.updatePassword(this.profile.id, this.passwordForm.value).subscribe({
      next: (res) => {
        if (res.success) {
          this.isPasswordUpdated = true;
          this.hidePasswordAlert = false;
        }
      }, error: (err) => {
        this.hidePasswordAlert = false;
        this.passwordError = err.message;
      }
    });
  }

  fadeOutMessage(): any {
    setTimeout(() => {
      this.hideProfileAlert = true;
      this.hidePasswordAlert = true;
      if (this.isPasswordUpdated) {
        this.auth.logout();
        window.location.reload();
      }
    }, 5000);
  }

  getStrengthWidth(passwordStrength: string): string {
    switch (passwordStrength) {
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
      case 1:
        return 'Weak';
      case 2:
        return 'Moderate';
      case 3:
        return 'Strong';
      case 4:
        return 'Very Strong';
      case 5:
        return 'Excellent'
      default:
        return 'Invalid';
    }
  }

  getTotpSetup(): void {
    this.http.post(this.apiURL +'/totp/setup', {}).subscribe(
      (response: any) => {
        this.qrCodeUrl = response.qrCodeUrl;
        this.secret = response.secret;
      },
      (error) => {
        if (error.status === 403) {
          this.errorMessage = 'Access denied: If you want to configure TOTP, please create your own account.';
        } else {
          this.errorMessage = 'Failed to load TOTP setup details.';
        }
      }
    );
  }

  verifyTotp(): void {
    this.http.post(this.apiURL +'/totp/verify', { totp: this.totpForm.get('totpCode').value }).subscribe(
      () => {
        this.successMessage = 'TOTP verified and enabled successfully.';
        this.errorMessage = '';
      },
      (error) => {
        this.errorMessage = 'Invalid TOTP code. Please try again.';
        this.successMessage = '';
      }
    );
  }
}
