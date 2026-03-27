import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ReactiveFormsModule} from "@angular/forms";
import {TranslocoDirective} from "@jsverse/transloco";
import {PasswordInputComponent} from "../shared/password-input/password-input.component";
import {FaIconComponent, FaIconLibrary, FontAwesomeModule} from "@fortawesome/angular-fontawesome";
import {faEye, faEyeSlash} from "@fortawesome/free-solid-svg-icons";
import {TitleCasePipe} from "./titlecase.pipe";
import {ReplaceUnderscoresPipe} from "./replaceunderscores.pipe";

@NgModule({
  imports: [
    CommonModule,
    ReactiveFormsModule,
    TranslocoDirective,
    FaIconComponent,
    FontAwesomeModule,
    PasswordInputComponent,
    TitleCasePipe,
    ReplaceUnderscoresPipe
  ],
  exports: [
    PasswordInputComponent,
    CommonModule,
    ReactiveFormsModule,
    TitleCasePipe,
    ReplaceUnderscoresPipe
  ]
})
export class SharedModule {
  constructor(library: FaIconLibrary) {
    library.addIcons(faEye, faEyeSlash);
  }
}
