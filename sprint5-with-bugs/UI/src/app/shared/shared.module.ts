import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ReactiveFormsModule} from "@angular/forms";
import {PasswordInputComponent} from "../shared/password-input/password-input.component";

@NgModule({
  imports: [
    CommonModule,
    ReactiveFormsModule,
    PasswordInputComponent
  ],
  exports: [
    PasswordInputComponent,
    CommonModule,
    ReactiveFormsModule,
  ]
})
export class SharedModule {
}
