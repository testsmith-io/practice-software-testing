// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

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
  // Phone may contain digits, spaces and ( ) + - only; mirrors the API rule.
  static readonly PHONE_PATTERN = /^\+?[0-9\s().-]{7,24}$/;

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
      });

    this.profileForm = new FormGroup({
      first_name: new FormControl('', [Validators.required]),
      last_name: new FormControl('', [Validators.required]),
      email: new FormControl('', [Validators.required]),
      phone: new FormControl('', [Validators.required, Validators.pattern(ProfileComponent.PHONE_PATTERN)]),
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
    // Clear any state from a previous submission so a stale success and a new
    // error (or vice versa) can never be shown at the same time.
    this.isProfileUpdated = false;
    this.profileError = '';
    this.hideProfileAlert = false;

    if (this.profileForm.invalid) {
      this.profileForm.markAllAsTouched();
      this.profileError = this.f['phone'].errors?.['pattern']
        ? 'Please enter a valid phone number (digits, spaces and ( ) + - only).'
        : 'Please correct the highlighted fields before saving.';
      return;
    }

    this.customerAccountService.update(this.id, this.profileForm.value).subscribe({
      next: (res) => {
        if (res.success) {
          this.isProfileUpdated = true;
          this.hideProfileAlert = false;
        }
      }, error: (err) => {
        this.hideProfileAlert = false;
        this.profileError = this.formatError(err);
      }
    });
  }

  /**
   * Flatten an API error into a readable message. Laravel validation errors
   * arrive as { field: string[] }, but auth/other failures arrive as a plain
   * { message: string } or a string. Only spread arrays — spreading a string
   * would push it one character at a time and render vertically.
   */
  private formatError(err: any): string {
    if (!err) {
      return 'An unexpected error occurred.';
    }
    if (typeof err === 'string') {
      return err;
    }
    const messages: string[] = [];
    for (const field of Object.keys(err)) {
      const value = err[field];
      if (Array.isArray(value)) {
        messages.push(...value.filter((v) => typeof v === 'string'));
      } else if (typeof value === 'string') {
        messages.push(value);
      }
    }
    return messages.length ? messages.join('\n') : 'An unexpected error occurred.';
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
