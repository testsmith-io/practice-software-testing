import {Component, inject, OnInit} from '@angular/core';
import {CustomerAccountService} from "../../shared/customer-account.service";
import {first} from "rxjs/operators";
import {FormControl, FormGroup, ReactiveFormsModule, Validators} from "@angular/forms";
import {NgClass} from "@angular/common";

@Component({
  selector: 'app-profile',
  templateUrl: './profile.component.html',
  imports: [
    ReactiveFormsModule,
    NgClass
  ],
  styleUrls: []
})
export class ProfileComponent implements OnInit {
  private readonly customerAccountService = inject(CustomerAccountService);
  private readonly auth = inject(CustomerAccountService);

  id!: number;
  profileForm!: FormGroup;
  passwordForm!: FormGroup;
  isProfileUpdated: boolean = false;
  isPasswordUpdated: boolean = false;
  passwordError: string;
  profileError: string;
  hideProfileAlert: boolean = false;
  hidePasswordAlert: boolean = false;

  ngOnInit(): void {
    this.customerAccountService.getDetails()
      .pipe(first())
      .subscribe((profile) => {
          this.id = profile.id;
          this.profileForm.patchValue(profile);
        },
        (error) => {
          if (error.status === 401 || error.status === 403) {
            window.localStorage.removeItem('TOKEN_KEY');
            window.location.href = '/#/auth/login';
          }
        });

    this.profileForm = new FormGroup({
      first_name: new FormControl('', [Validators.required]),
      last_name: new FormControl('', [Validators.required]),
      email: new FormControl('', [Validators.required]),
      phone: new FormControl('', [Validators.required]),
      address: new FormControl('', [Validators.required]),
      state: new FormControl('', [Validators.required]),
      country: new FormControl('', [Validators.required]),
      postcode: new FormControl('', [Validators.required]),
      city: new FormControl('', [Validators.required]),
    });

    this.passwordForm = new FormGroup({
      current_password: new FormControl('', [Validators.required]),
      new_password: new FormControl('', [Validators.required]),
      new_password_confirmation: new FormControl('', [Validators.required]),
    });
  }

  get f() {
    return this.profileForm.controls;
  }

  updateProfile() {
    this.customerAccountService.update(this.id, this.profileForm.value).subscribe({
      next: (res) => {
        if (res.success) {
          this.isProfileUpdated = true;
          this.hideProfileAlert = false;
        }
      }, error: (err) => {
        this.hideProfileAlert = false;
        this.profileError = Object.values(err).join('\r\n');
      }
    });
  }

  updatePassword() {
    this.customerAccountService.updatePassword(this.id, this.passwordForm.value).subscribe({
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
    }, 3000);
  }

}
