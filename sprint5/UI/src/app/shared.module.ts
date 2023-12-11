import {NgModule} from '@angular/core';
import {TitleCasePipe} from "./_helpers/titlecase.pipe";
import {ReplaceUnderscoresPipe} from "./_helpers/replaceunderscores.pipe";

@NgModule({
  declarations: [
    TitleCasePipe,
    ReplaceUnderscoresPipe,
  ],
  imports: [
  ],
  exports: [
    TitleCasePipe,
    ReplaceUnderscoresPipe
  ]
})
export class SharedModule {
}
