import {Component, OnInit, ViewChild} from '@angular/core';
import {AbstractControl, FormBuilder, FormGroup, Validators} from "@angular/forms";
import {Observable, of, map} from "rxjs";
import {CartService} from "../_services/cart.service";
import {CustomerAccountService} from "../shared/customer-account.service";
import {TokenStorageService} from "../_services/token-storage.service";
import {InvoiceService} from "../_services/invoice.service";
import {PaymentService} from "../_services/payment.service";
import {environment} from "../../environments/environment";
import {Product} from "../models/product";
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
