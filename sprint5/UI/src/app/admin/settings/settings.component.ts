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
      geolocation: ['', []],
      co2Scale: ['', []],
      ecoBadge: ['', []]
    });
    this.form.controls['endpoint'].setValue(window.localStorage.getItem('PAYMENT_ENDPOINT'));
    this.form.controls['geolocation'].setValue(window.localStorage.getItem('RETRIEVE_GEOLOCATION'));

    // CO₂ scale is enabled by default
    const co2ScaleSetting = window.localStorage.getItem('CO2_SCALE_ENABLED');
    this.form.controls['co2Scale'].setValue(co2ScaleSetting === null ? true : co2ScaleSetting === 'true');

    // Eco badge is enabled by default, only store if user explicitly disables it
    const ecoBadgeSetting = window.localStorage.getItem('ECO_BADGE_ENABLED');
    this.form.controls['ecoBadge'].setValue(ecoBadgeSetting === null ? true : ecoBadgeSetting === 'true');
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
    const co2Scale = this.form.controls['co2Scale'].value;
    const ecoBadge = this.form.controls['ecoBadge'].value;

    if (endpoint !== '' && endpoint !== null) {
      window.localStorage.setItem('PAYMENT_ENDPOINT', endpoint);
    }

    if (geolocation) {
      window.localStorage.setItem('RETRIEVE_GEOLOCATION', geolocation);
    }

    // Store CO₂ scale setting (true by default)
    window.localStorage.setItem('CO2_SCALE_ENABLED', co2Scale ? 'true' : 'false');

    // Store eco badge setting (true by default)
    window.localStorage.setItem('ECO_BADGE_ENABLED', ecoBadge ? 'true' : 'false');

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
    window.localStorage.removeItem('CO2_SCALE_ENABLED');
    window.localStorage.removeItem('ECO_BADGE_ENABLED');
    window.localStorage.removeItem('cart_id');
    window.localStorage.removeItem('cart_quantity');
  }

  isCo2ScaleEnabled(): boolean {
    return this.form.controls['co2Scale'].value === true;
  }
}
