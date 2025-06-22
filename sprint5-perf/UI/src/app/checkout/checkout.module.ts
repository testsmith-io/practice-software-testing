import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CheckoutRoutingModule } from './checkout-routing.module';
import { CheckoutComponent } from './checkout.component';
import {FormsModule, ReactiveFormsModule} from "@angular/forms";
import {ArchwizardModule} from "@y3krulez/angular-archwizard";
import {CartComponent} from "./cart/cart.component";
import {LoginComponent} from "./login/login.component";
import {AddressComponent} from "./address/address.component";
import {PaymentComponent} from "./payment/payment.component";
import {TranslocoDirective} from "@jsverse/transloco";
import {FaIconComponent, FaIconLibrary, FontAwesomeModule} from "@fortawesome/angular-fontawesome";
import {faRemove} from "@fortawesome/free-solid-svg-icons";


@NgModule({
  declarations: [
    CheckoutComponent,
    CartComponent,
    LoginComponent,
    AddressComponent,
    PaymentComponent
  ],
  imports: [
    CommonModule,
    CheckoutRoutingModule,
    FormsModule,
    ArchwizardModule,
    ReactiveFormsModule,
    TranslocoDirective,
    FaIconComponent,
    FontAwesomeModule
  ]
})
export class CheckoutModule {
  constructor(library: FaIconLibrary) {
    library.addIcons(faRemove);
  }
}
