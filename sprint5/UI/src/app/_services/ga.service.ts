import { Injectable } from '@angular/core';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class GaService {

  constructor() {
    if (environment.gaCode) {
      this.injectGaScript(environment.gaCode);
    }
  }

  private injectGaScript(gaCode: string): void {
    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${gaCode}`;
    document.head.appendChild(script);

    const scriptText = document.createElement('script');
    scriptText.innerHTML = `
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '${gaCode}', {'page_path': location.pathname + location.hash});
    `;
    document.head.appendChild(scriptText);
  }
}
