import {Component, OnInit} from '@angular/core';
import {AbstractControl, FormBuilder, FormGroup, Validators} from "@angular/forms";
import {ContactService} from "../_services/contact.service";
import {ContactMessage} from "../models/contact-message";
import {CustomerAccountService} from "../shared/customer-account.service";
import {BrowserDetectorService} from "../_services/browser-detector.service";

@Component({
  selector: 'app-contact',
  templateUrl: './contact.component.html',
  styleUrls: ['./contact.component.css']
})
export class ContactComponent implements OnInit {
  contact: FormGroup | any;
  submitted: boolean;
  error: string;
  showConfirmation: boolean = false;
  role: string = '';
  name: string = '';
  private file: File = null;

  constructor(private formBuilder: FormBuilder,
              private contactService: ContactService,
              private auth: CustomerAccountService,
              public browserDetect: BrowserDetectorService) {
  }

  ngOnInit(): void {
    this.contact = this.formBuilder.group(
      {
        first_name: ['', []],
        last_name: ['', []],
        email: ['', [Validators.pattern("^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$")]],
        attachment: ['', []],
        subject: ['', [Validators.required]],
        message: ['', [Validators.required, Validators.minLength(50)]]
      }
    );
    this.getSignedInUser();
  }

  getSignedInUser() {
    this.auth.getDetails().subscribe(res => {
      this.role = this.auth.getRole();
      this.name = res.first_name + ' ' + res.last_name;
    }, () => {
      this.contact.get('first_name').setValidators(Validators.required);
      this.contact.get('last_name').setValidators(Validators.required);
      this.contact.get('email').setValidators(Validators.required);
      this.contact.controls['first_name'].updateValueAndValidity();
      this.contact.controls['last_name'].updateValueAndValidity();
      this.contact.controls['email'].updateValueAndValidity();
    })
  }

  get f(): { [key: string]: AbstractControl } {
    return this.contact.controls;
  }

  changeFile(fileEvent: any) {
    const file: File = fileEvent.target.files[0];

    if (Math.round((file.size / 1024) / 1024) > 0.5) {
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
      name: this.contact.value.first_name + ' ' + this.contact.value.last_name,
      email: this.contact.value.email,
      subject: this.contact.value.subject,
      message: this.contact.value.message,
      status: 'NEW'
    };

    this.contactService.sendMessage(this.file, payload).subscribe({
      next: () => {
        this.showConfirmation = true;
      }, error: (err) => {
        this.error = Object.values(err).join('\r\n');
      }
    });
  }
}
