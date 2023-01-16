import {Injectable} from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class BrowserDetectorService {

  agent: any;

  constructor() {
    this.agent = window.navigator.userAgent.toLowerCase();
  }

  isFirefox(): boolean {
    return this.getBrowserName() === 'Firefox';
  }

  isChrome(): boolean {
    return this.getBrowserName() === 'Chrome';
  }

  isSafari(): boolean {
    return this.getBrowserName() === 'Safari';
  }

  isEdge(): boolean {
    return this.getBrowserName() === 'Microsoft Edge';

  }

  getBrowserVersion(): string {
    if (this.isFirefox()) {
      return this.agent.split('firefox/')[1].split('.')[0];
    } else if (this.isChrome()) {
      return this.agent.split('chrome/')[1].split('.')[0];
    } else if (this.isEdge()) {
      return this.agent.split('edg/')[1].split('.')[0];
    } else if (this.isSafari()) {
      return this.agent.split('version/')[1].split('.')[0];
    } else {
      return '';
    }
  }

  isMobile(): boolean {
    return window.navigator.maxTouchPoints > 0;
  }

  private getBrowserName(): string {
    return this.agent.indexOf('edge') > -1 ? 'Microsoft Edge'
      : this.agent.indexOf('edg') > -1 ? 'Chromium-based Edge'
        : this.agent.indexOf('opr') > -1 ? 'Opera'
          : this.agent.indexOf('chrome') > -1 ? 'Chrome'
            : this.agent.indexOf('trident') > -1 ? 'Internet Explorer'
              : this.agent.indexOf('firefox') > -1 ? 'Firefox'
                : this.agent.indexOf('safari') > -1 ? 'Safari'
                  : 'other';
  }

}
