import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';

import {PrivacyRoutingModule} from './privacy-routing.module';
import {PrivacyComponent} from './privacy.component';
import {TranslocoDirective} from "@jsverse/transloco";


@NgModule({
  imports: [
    CommonModule,
    PrivacyRoutingModule,
    TranslocoDirective,
    PrivacyComponent
  ]
})
export class PrivacyModule { }
