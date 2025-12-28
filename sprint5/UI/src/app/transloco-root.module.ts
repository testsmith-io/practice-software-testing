import {provideTransloco, TranslocoModule} from '@jsverse/transloco';
import {NgModule} from '@angular/core';
import {TranslocoHttpLoader} from './transloco-loader';
import {environment} from '../environments/environment';

const availableLangs = ['de', 'en', 'fr', 'es', 'nl', 'tr'];

function getDefaultLanguage(): string {
  // First check if user has explicitly set a language preference
  const storedLang = localStorage.getItem('language');
  if (storedLang && availableLangs.includes(storedLang)) {
    return storedLang;
  }

  // Detect browser language
  const browserLang = navigator.language || (navigator as any).userLanguage;
  if (browserLang) {
    // Extract the primary language code (e.g., 'en-US' -> 'en')
    const langCode = browserLang.split('-')[0].toLowerCase();
    if (availableLangs.includes(langCode)) {
      return langCode;
    }
  }

  // Fallback to English
  return 'en';
}

@NgModule({
  exports: [ TranslocoModule ],
  providers: [
      provideTransloco({
        config: {
          availableLangs: availableLangs,
          fallbackLang: 'en',
          defaultLang: getDefaultLanguage(),
          reRenderOnLangChange: true,
          prodMode: environment.production,
        },
        loader: TranslocoHttpLoader
      }),
  ],
})
export class TranslocoRootModule {}
