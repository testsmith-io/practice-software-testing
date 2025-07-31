import {Component, inject, OnInit} from '@angular/core';
import {AbstractControl, FormBuilder, FormGroup, ReactiveFormsModule} from "@angular/forms";
import {CustomerAccountService} from "../../shared/customer-account.service";
import {BrowserDetectorService} from "../../_services/browser-detector.service";
import {NgClass, NgStyle} from "@angular/common";

@Component({
  selector: 'app-forgot-password',
  templateUrl: './forgot-password.component.html',
  imports: [
    ReactiveFormsModule,
    NgClass,
    NgStyle
  ],
  styleUrls: []
})
export class ForgotPasswordComponent implements OnInit {
  private readonly formBuilder = inject(FormBuilder);
  private readonly accountService = inject(CustomerAccountService);
  public readonly browserDetect = inject(BrowserDetectorService);

  form: FormGroup | any;
  isUpdated: boolean = false;
  hideAlert: boolean = false;
  submitted = false;
  error: string | undefined;

  ngOnInit(): void {
    this.form = this.formBuilder.group(
      {
        email: ['', []]
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
        this.error = Object.values(err).join('\r\n');
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
