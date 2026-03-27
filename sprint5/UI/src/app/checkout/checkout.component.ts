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
  addressData: FormGroup;

  handleCusAddressChange(cusAddress: FormGroup) {
    this.addressData = cusAddress.value.address;
    this.canExitStep3 = cusAddress.valid;
  }

  enterAddressStep($event: any) {
    this.addressComponent.setAddress();
  }

}
