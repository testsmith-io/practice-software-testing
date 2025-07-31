import {provideTransloco, TranslocoModule} from '@jsverse/transloco';
import {NgModule} from '@angular/core';
import {TranslocoHttpLoader} from './transloco-loader';
import {environment} from '../environments/environment';

@NgModule({
  exports: [ TranslocoModule ],
  providers: [
      provideTransloco({
        config: {
          availableLangs: ['de', 'en', 'fr', 'es', 'nl', 'tr'],
          fallbackLang: 'en',
          defaultLang: localStorage.getItem('language') || 'en',
          reRenderOnLangChange: true,
          prodMode: environment.production,
        },
        loader: TranslocoHttpLoader
      }),
  ],
})
export class TranslocoRootModule {}
