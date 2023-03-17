import {Component, OnInit} from '@angular/core';
import {AbstractControl, FormBuilder, FormGroup} from "@angular/forms";
import {CustomerAccountService} from "../../shared/customer-account.service";

@Component({
  selector: 'app-forgot-password',
  templateUrl: './forgot-password.component.html',
  styleUrls: ['./forgot-password.component.css']
})
export class ForgotPasswordComponent implements OnInit {
  form: FormGroup | any;
  isUpdated: boolean = false;
  hideAlert: boolean = false;
  submitted = false;
  error: string | undefined;

  constructor(private formBuilder: FormBuilder,
              private accountService: CustomerAccountService) {
  }

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
