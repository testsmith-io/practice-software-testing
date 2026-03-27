import {Component, inject, OnInit} from '@angular/core';
import {FormBuilder, FormGroup, ReactiveFormsModule} from "@angular/forms";
import {NgClass} from "@angular/common";
import {RouterLink} from "@angular/router";

@Component({
  selector: 'app-settings',
  templateUrl: './settings.component.html',
  imports: [
    ReactiveFormsModule,
    NgClass,
    RouterLink
  ],
  styleUrls: []
})
export class SettingsComponent implements OnInit {
  private readonly formBuilder = inject(FormBuilder);

  form: FormGroup;
  id: string;
  submitted: boolean = false;
  isUpdated: boolean = false;
  hideAlert: boolean = false;
  error: string;

  ngOnInit(): void {
    this.form = this.formBuilder.group({
      endpoint: ['', []],
      geolocation: ['', []]
    });
    this.form.controls['endpoint'].setValue(window.sessionStorage.getItem('PAYMENT_ENDPOINT'));
    this.form.controls['geolocation'].setValue(window.sessionStorage.getItem('RETRIEVE_GEOLOCATION'));
  }

  get f() {
    return this.form.controls;
  }

  onSubmit() {
    this.submitted = true;
    this.hideAlert = false;

    if (this.form.invalid) {
      return;
    }

    window.sessionStorage.setItem('PAYMENT_ENDPOINT', this.form.controls['endpoint'].value);
    window.sessionStorage.setItem('RETRIEVE_GEOLOCATION', this.form.controls['geolocation'].value);
    this.isUpdated = true;
  }

  fadeOutMessage(): any {
    setTimeout(() => {
      this.hideAlert = true;
      this.isUpdated = false;
    }, 3000);
  }


  clearStorage() {
    window.sessionStorage.removeItem('PAYMENT_ENDPOINT');
    window.sessionStorage.removeItem('RETRIEVE_GEOLOCATION');
    window.sessionStorage.removeItem('GEO_LOCATION');
    window.sessionStorage.removeItem('cart');
  }
}
