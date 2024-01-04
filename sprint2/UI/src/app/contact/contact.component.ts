import {Component, OnInit} from '@angular/core';
import {AbstractControl, FormBuilder, FormGroup, Validators} from "@angular/forms";
import {ContactMessage} from "../models/contact-message";
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

  constructor(private formBuilder: FormBuilder,
              public browserDetect: BrowserDetectorService) {
  }

  ngOnInit(): void {
    this.contact = this.formBuilder.group(
      {
        first_name: ['', []],
        last_name: ['', []],
        email: ['', [Validators.pattern("^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$")]],
        subject: ['', [Validators.required]],
        message: ['', [Validators.required, Validators.minLength(50)]]
      }
    );
  }

  get f(): { [key: string]: AbstractControl } {
    return this.contact.controls;
  }

  onSubmit() {
    this.submitted = true;

    if (this.contact.invalid) {
      return;
    }

    this.showConfirmation = true;

  }
}
