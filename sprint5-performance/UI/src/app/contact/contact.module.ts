import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';

import {ContactRoutingModule} from './contact-routing.module';
import {ContactComponent} from './contact.component';
import {FormsModule, ReactiveFormsModule} from "@angular/forms";
import {TranslocoDirective} from "@jsverse/transloco";


@NgModule({
  imports: [
    CommonModule,
    ContactRoutingModule,
    FormsModule,
    ReactiveFormsModule,
    TranslocoDirective,
    ContactComponent
  ]
})
export class ContactModule { }
