// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

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

    const co2Setting = window.localStorage.getItem('CO2_SCALE_ENABLED');
    this.form.controls['co2Scale'].setValue(co2Setting === null || co2Setting === 'true');

    const ecoBadgeSetting = window.localStorage.getItem('ECO_BADGE_ENABLED');
    this.form.controls['ecoBadge'].setValue(ecoBadgeSetting === null || ecoBadgeSetting === 'true');
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

    const co2Scale = this.form.controls['co2Scale'].value;
    window.localStorage.setItem('CO2_SCALE_ENABLED', co2Scale ? 'true' : 'false');

    const ecoBadge = this.form.controls['ecoBadge'].value;
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
    window.localStorage.removeItem('cart_id');
    window.localStorage.removeItem('cart_quantity');
    window.localStorage.removeItem('CO2_SCALE_ENABLED');
    window.localStorage.removeItem('ECO_BADGE_ENABLED');
  }

  isCo2ScaleEnabled(): boolean {
    return this.form.controls['co2Scale'].value === true;
  }
}
