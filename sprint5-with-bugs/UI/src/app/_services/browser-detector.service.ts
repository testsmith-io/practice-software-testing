// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

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
    return this.agent.includes('edge') ? 'Microsoft Edge'
      : this.agent.includes('edg') ? 'Microsoft Edge'
        : this.agent.includes('opr') ? 'Opera'
          : this.agent.includes('chrome') ? 'Chrome'
            : this.agent.includes('trident') ? 'Internet Explorer'
              : this.agent.includes('firefox') ? 'Firefox'
                : this.agent.includes('safari') ? 'Safari'
                  : 'other';
  }

}
