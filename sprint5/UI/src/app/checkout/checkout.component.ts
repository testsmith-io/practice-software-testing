// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Component, ViewChild} from '@angular/core';
import {FormGroup} from "@angular/forms";
import {AddressComponent} from "./address/address.component";
import {ArchwizardModule} from "@y3krulez/angular-archwizard";
import {CartComponent} from "./cart/cart.component";
import {LoginComponent} from "./login/login.component";
import {PaymentComponent} from "./payment/payment.component";
import {TranslocoDirective} from "@jsverse/transloco";

@Component({
  selector: 'app-checkout',
  templateUrl: './checkout.component.html',
  imports: [
    ArchwizardModule,
    CartComponent,
    LoginComponent,
    AddressComponent,
    PaymentComponent,
    TranslocoDirective
  ],
  styleUrls: []
})
export class CheckoutComponent {

  @ViewChild(AddressComponent) addressComponent: AddressComponent;

  canExitStep3 = true;
  // Holds the address form's value object, but is bound both to <app-address>
  // (which treats it as a form control) and <app-payment> (which treats it as
  // a plain address object) — so it stays loosely typed to satisfy both.
  addressData: any;

  handleCusAddressChange(cusAddress: FormGroup) {
    this.addressData = cusAddress.value.address;
    this.canExitStep3 = cusAddress.valid;
  }

  enterAddressStep($event: unknown) {
    this.addressComponent.setAddress();
  }

}
