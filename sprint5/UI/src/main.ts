import {enableProdMode, importProvidersFrom} from '@angular/core';
import {bootstrapApplication} from '@angular/platform-browser';
import {AppComponent} from './app/app.component';
import {AppRoutingModule} from './app/app-routing.module';
import {HTTP_INTERCEPTORS, HttpClientModule} from '@angular/common/http';
import {BrowserAnimationsModule} from '@angular/platform-browser/animations';
import {ToastrModule} from 'ngx-toastr';
import {TranslocoRootModule} from './app/transloco-root.module';
import {AuthInterceptor} from './app/_helpers/auth.interceptor';
import {ContentTypeInterceptor} from './app/_helpers/contenttype.interceptor';
import {GaService} from './app/_services/ga.service';
import {UserAuthGuard} from './app/UserAuthGuard';
import {AdminAuthGuard} from './app/AdminAuthGuard';
import {FontAwesomeModule} from '@fortawesome/angular-fontawesome';

import {environment} from './environments/environment';

if (environment.production) {
  enableProdMode();
}

bootstrapApplication(AppComponent, {
  providers: [
    importProvidersFrom(
      AppRoutingModule,
      HttpClientModule,
      BrowserAnimationsModule,
      FontAwesomeModule,
      ToastrModule.forRoot(),
      TranslocoRootModule
    ),
    { provide: HTTP_INTERCEPTORS, useClass: AuthInterceptor, multi: true },
    { provide: HTTP_INTERCEPTORS, useClass: ContentTypeInterceptor, multi: true },
    GaService,
    UserAuthGuard,
    AdminAuthGuard
  ]
});
