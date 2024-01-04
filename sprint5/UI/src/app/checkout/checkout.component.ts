import {Component, ViewChild} from '@angular/core';
import {FormGroup } from "@angular/forms";
import {AddressComponent} from "./address/address.component";

@Component({
  selector: 'app-checkout',
  templateUrl: './checkout.component.html',
  styleUrls: ['./checkout.component.css']
})
export class CheckoutComponent {

  @ViewChild(AddressComponent) addressComponent: AddressComponent;

  canExitStep3 = true;
  addressData: FormGroup;

  handleCusAddressChange(cusAddress: FormGroup) {
    this.addressData = cusAddress;
    this.canExitStep3 = cusAddress.valid;
  }

  enterAddressStep($event: any) {
    this.addressComponent.setAddress();
  }

}
