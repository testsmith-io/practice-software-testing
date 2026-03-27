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
    this.form.controls['endpoint'].setValue(window.localStorage.getItem('PAYMENT_ENDPOINT'));
    this.form.controls['geolocation'].setValue(window.localStorage.getItem('RETRIEVE_GEOLOCATION'));
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

    const endpoint = this.form.controls['endpoint'].value;
    const geolocation = this.form.controls['geolocation'].value;

    if (endpoint !== '' && endpoint !== null) {
      window.localStorage.setItem('PAYMENT_ENDPOINT', endpoint);
    }

    if (geolocation) {
      window.localStorage.setItem('RETRIEVE_GEOLOCATION', geolocation);
    }

    this.isUpdated = true;
  }

  fadeOutMessage(): any {
    setTimeout(() => {
      this.hideAlert = true;
      this.isUpdated = false;
    }, 3000);
  }


  clearStorage() {
    window.localStorage.removeItem('PAYMENT_ENDPOINT');
    window.localStorage.removeItem('RETRIEVE_GEOLOCATION');
    window.localStorage.removeItem('GEO_LOCATION');
    window.localStorage.removeItem('cart_id');
    window.localStorage.removeItem('cart_quantity');
  }
}
