import {Component, inject, OnInit} from '@angular/core';
import {AbstractControl, FormBuilder, FormGroup, ReactiveFormsModule, Validators} from "@angular/forms";
import {BrowserDetectorService} from "../_services/browser-detector.service";
import {NgClass} from "@angular/common";

@Component({
  selector: 'app-contact',
  templateUrl: './contact.component.html',
  imports: [
    ReactiveFormsModule,
    NgClass
],
  styleUrls: []
})
export class ContactComponent implements OnInit {
  private readonly formBuilder = inject(FormBuilder);
  public readonly browserDetect = inject(BrowserDetectorService);

  contact: FormGroup | any;
  submitted: boolean;
  error: string;
  showConfirmation: boolean = false;
  role: string = '';
  name: string = '';

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
