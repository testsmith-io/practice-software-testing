import {Component, inject, OnInit} from '@angular/core';
import {AbstractControl, FormBuilder, FormGroup, ReactiveFormsModule, Validators} from "@angular/forms";
import {ContactService} from "../_services/contact.service";
import {ContactMessage} from "../models/contact-message";
import {CustomerAccountService} from "../shared/customer-account.service";
import {BrowserDetectorService} from "../_services/browser-detector.service";
import {NgClass} from "@angular/common";
import {TranslocoDirective} from "@jsverse/transloco";
import {catchError, of} from "rxjs";
import {tap} from "rxjs/operators";

@Component({
  selector: 'app-contact',
  templateUrl: './contact.component.html',
  imports: [
    ReactiveFormsModule,
    NgClass,
    TranslocoDirective
  ],
  styleUrls: []
})
export class ContactComponent implements OnInit {
  private formBuilder = inject(FormBuilder);
  private contactService = inject(ContactService);
  private auth = inject(CustomerAccountService);
  public browserDetect = inject(BrowserDetectorService);

  contact: FormGroup | any;
  submitted: boolean;
  error: string;
  showConfirmation: boolean = false;
  role: string = '';
  name: string = '';
  private file: File = null;

  ngOnInit(): void {
    this.contact = this.formBuilder.group(
      {
        first_name: ['', []],
        last_name: ['', []],
        email: ['', [Validators.email]],
        attachment: ['', []],
        subject: ['', [Validators.required]],
        message: ['', [Validators.required, Validators.minLength(50)]]
      }
    );
    this.getSignedInUser();
  }

  getSignedInUser() {
    this.auth.getDetails().pipe(
      tap(res => {
        this.role = this.auth.getRole();
        this.name = res.first_name + ' ' + res.last_name;
      }),
      catchError(() => {
        this.contact.get('first_name')?.setValidators(Validators.required);
        this.contact.get('last_name')?.setValidators(Validators.required);
        this.contact.get('email')?.setValidators([Validators.required, Validators.email]);

        this.contact.get('first_name')?.updateValueAndValidity();
        this.contact.get('last_name')?.updateValueAndValidity();
        this.contact.get('email')?.updateValueAndValidity();

        return of(null); // avoid breaking the stream
      })
    ).subscribe();
  }

  get f(): { [key: string]: AbstractControl } {
    return this.contact.controls;
  }

  changeFile(fileEvent: any) {
    const file: File = fileEvent.target.files[0];
    if (file.type !== 'text/plain') {
      this.contact.controls['attachment'].setErrors({'incorrectType': true});
    }
    if (file.size !== 0) {
      this.contact.controls['attachment'].setErrors({'incorrectSize': true});
    }
    this.file = file;
  }

  onSubmit() {
    this.submitted = true;

    if (this.contact.invalid) {
      return;
    }

    const payload: ContactMessage = {
      name: this.name ? this.name : `${this.contact.value.first_name} ${this.contact.value.last_name}`,
      subject: this.contact.value.subject,
      message: this.contact.value.message
    };

    if (this.contact.value.email) {
      payload.email = this.contact.value.email;
    }

    this.contactService.sendMessage(this.file, payload).subscribe({
      next: () => {
        this.showConfirmation = true;
      }, error: (err) => {
        this.error = Object.values(err).join('\r\n');
      }
    });
  }
}
