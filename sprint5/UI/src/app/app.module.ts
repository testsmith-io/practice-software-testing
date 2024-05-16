import {NgModule} from '@angular/core';
import {BrowserModule} from '@angular/platform-browser';

import {AppRoutingModule} from './app-routing.module';
import {AppComponent} from './app.component';
import {HTTP_INTERCEPTORS, HttpClientModule} from "@angular/common/http";
import {HeaderComponent} from './header/header.component';
import {FooterComponent} from './footer/footer.component';
import {FormsModule, ReactiveFormsModule} from "@angular/forms";
import {CheckoutComponent} from './checkout/checkout.component';
import {ContactComponent} from './contact/contact.component';
import {UserAuthGuard} from "./UserAuthGuard";
import {AuthInterceptor} from "./_helpers/auth.interceptor";
import {AdminAuthGuard} from "./AdminAuthGuard";
import {ContentTypeInterceptor} from "./_helpers/contenttype.interceptor";
import {CategoryComponent} from './products/category/category.component';
import {OverviewComponent} from './products/rentals/overview/overview.component';
import {OverviewComponent as ProductOverviewComponent} from './products/overview/overview.component';
import {DetailComponent as ProductDetailComponent} from './products/detail/detail.component';
import {NgHttpLoaderModule} from "ng-http-loader";
import { PrivacyComponent } from './privacy/privacy.component';
import {PaginationComponent} from "./pagination/pagination.component";
import {CartComponent} from "./checkout/cart/cart.component";
import {LoginComponent} from "./checkout/login/login.component";
import {AddressComponent} from "./checkout/address/address.component";
import {PaymentComponent} from "./checkout/payment/payment.component";
import {GaService} from "./_services/ga.service";
import {ArchwizardModule} from "@y3krulez/angular-archwizard";
import {NgxSliderModule} from "@angular-slider/ngx-slider";
import {CommonModule} from "@angular/common";
import { ToastrModule } from 'ngx-toastr';
import {RouterLink, RouterModule} from "@angular/router";
import {BrowserAnimationsModule} from "@angular/platform-browser/animations";
import { TranslocoRootModule } from './transloco-root.module';
import {TranslocoModule} from "@jsverse/transloco";

@NgModule({
  declarations: [
    AppComponent,
    HeaderComponent,
    FooterComponent,
    ProductDetailComponent,
    CheckoutComponent,
    ContactComponent,
    CategoryComponent,
    OverviewComponent,
    ProductOverviewComponent,
    PrivacyComponent,
    CartComponent,
    LoginComponent,
    AddressComponent,
    PaymentComponent
  ],
  imports: [
    BrowserModule,
    CommonModule,
    HttpClientModule,
    BrowserAnimationsModule,
    NgHttpLoaderModule.forRoot(),
    RouterModule,
    AppRoutingModule,
    FormsModule,
    ArchwizardModule,
    ReactiveFormsModule,
    NgxSliderModule,
    PaginationComponent,
    RouterLink,
    ToastrModule.forRoot(),
    TranslocoRootModule
  ],
  providers: [GaService, UserAuthGuard, AdminAuthGuard, {
    provide: HTTP_INTERCEPTORS,
    useClass: AuthInterceptor,
    multi: true
  }, {
    provide: HTTP_INTERCEPTORS,
    useClass: ContentTypeInterceptor,
    multi: true
  }],
  bootstrap: [AppComponent]
})
export class AppModule {
}
