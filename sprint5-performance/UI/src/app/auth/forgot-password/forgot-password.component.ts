import {Component, inject, OnInit} from '@angular/core';
import {AbstractControl, FormBuilder, FormGroup, ReactiveFormsModule, Validators} from "@angular/forms";
import {CustomerAccountService} from "../../shared/customer-account.service";
import {TranslocoDirective} from "@jsverse/transloco";
import {NgClass} from "@angular/common";

@Component({
  selector: 'app-forgot-password',
  templateUrl: './forgot-password.component.html',
  imports: [
    ReactiveFormsModule,
    TranslocoDirective,
    NgClass
  ],
  styleUrls: []
})
export class ForgotPasswordComponent implements OnInit {
  private formBuilder = inject(FormBuilder);
  private accountService = inject(CustomerAccountService);

  form: FormGroup | any;
  isUpdated: boolean = false;
  hideAlert: boolean = false;
  submitted = false;
  error: string | undefined;

  ngOnInit(): void {
    this.form = this.formBuilder.group(
      {
        email: ['', [Validators.required, Validators.pattern("^(?=.{1,256}$)[a-zA-Z0-9._%+-]{1,64}@[a-zA-Z0-9.-]{1,255}$")]]
      }
    );
  }

  get email() {
    return this.form.get('email');
  }

  get cf(): { [key: string]: AbstractControl } {
    return this.form.controls;
  }

  onSubmit(): void {
    this.submitted = true;

    if (this.form.invalid) {
      return;
    }

    const payload: any = {
      email: this.form.value.email
    };

    this.accountService.forgetPassword(payload).subscribe({
      next: () => {
        this.isUpdated = true;
        this.error = "";
      }, error: (err) => {
        this.error = err.message;
      }, complete: () => {
        this.hideAlert = false;
      }
    });
  }

  fadeOutMessage(): any {
    setTimeout(() => {
      this.hideAlert = true;
    }, 3000);
  }
}
