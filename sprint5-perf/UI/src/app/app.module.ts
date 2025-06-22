import {NgModule} from '@angular/core';
import {BrowserModule} from '@angular/platform-browser';

import {AppRoutingModule} from './app-routing.module';
import {AppComponent} from './app.component';
import {HTTP_INTERCEPTORS, HttpClientModule} from "@angular/common/http";
import {HeaderComponent} from './header/header.component';
import {FooterComponent} from './footer/footer.component';
import {UserAuthGuard} from "./UserAuthGuard";
import {AuthInterceptor} from "./_helpers/auth.interceptor";
import {AdminAuthGuard} from "./AdminAuthGuard";
import {ContentTypeInterceptor} from "./_helpers/contenttype.interceptor";
import {GaService} from "./_services/ga.service";
import {CommonModule} from "@angular/common";
import {RouterLink, RouterModule} from "@angular/router";
import {TranslocoRootModule} from './transloco-root.module';
import {ToastrModule} from "ngx-toastr";
import {BrowserAnimationsModule} from "@angular/platform-browser/animations";
import {FaIconComponent, FaIconLibrary, FontAwesomeModule} from "@fortawesome/angular-fontawesome";
import {faGlobe, faShoppingCart} from "@fortawesome/free-solid-svg-icons";

@NgModule({
  declarations: [
    AppComponent,
    HeaderComponent,
    FooterComponent,
  ],
  imports: [
    BrowserModule,
    CommonModule,
    HttpClientModule,
    RouterModule,
    AppRoutingModule,
    RouterLink,
    BrowserAnimationsModule,
    ToastrModule.forRoot(),
    TranslocoRootModule,
    FaIconComponent,
    FontAwesomeModule
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
  constructor(library: FaIconLibrary) {
    library.addIcons(faGlobe, faShoppingCart);
  }
}
