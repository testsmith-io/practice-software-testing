import {Component, OnInit} from '@angular/core';
import {FormBuilder, FormGroup} from "@angular/forms";

@Component({
  selector: 'app-settings',
  templateUrl: './settings.component.html',
  styleUrls: ['./settings.component.css']
})
export class SettingsComponent implements OnInit {
  form: FormGroup;
  id: string;
  submitted: boolean = false;
  isUpdated: boolean = false;
  hideAlert: boolean = false;
  error: string;

  constructor(
    private formBuilder: FormBuilder
  ) {
  }

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
