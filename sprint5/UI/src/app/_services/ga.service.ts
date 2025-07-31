import {Injectable} from '@angular/core';
import {environment} from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class GaService {

  constructor() {
    if (environment.gaCode) {
      this.injectGaScript(environment.gaCode);
      this.injectGtmScript(environment.gtmCode);
      this.injectGtmNoScript(environment.gtmCode);
    }
  }

  private injectGaScript(gaCode: string): void {
    // Inject GA external script
    const script = this.createScript();
    script.src = `https://www.googletagmanager.com/gtag/js?id=${gaCode}`;
    document.head.appendChild(script);

    // Inject GA initialization script
    const scriptText = this.createScript();
    scriptText.innerHTML = `
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '${gaCode}');
    `;
    document.head.appendChild(scriptText);
  }

  private injectGtmScript(gtmId: string): void {
    const script = this.createScript();
    script.innerHTML = `
      (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
      new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
      j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
      'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
      })(window,document,'script','dataLayer','${gtmId}');
    `;
    document.head.appendChild(script);
  }

  private injectGtmNoScript(gtmId: string): void {
    const noscript = document.createElement('noscript');
    noscript.innerHTML = `
      <iframe src="https://www.googletagmanager.com/ns.html?id=${gtmId}"
      height="0" width="0" style="display:none;visibility:hidden"></iframe>
    `;
    document.body.insertBefore(noscript, document.body.firstChild);
  }

  private createScript(): HTMLScriptElement {
    const script = document.createElement('script');
    script.defer = true;
    return script;
  }
}
