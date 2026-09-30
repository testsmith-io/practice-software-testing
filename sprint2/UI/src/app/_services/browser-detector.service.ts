// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Injectable} from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class BrowserDetectorService {

  agent: string;

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

  // Ordered so the more specific tokens win (e.g. 'edge' before 'edg',
  // 'edg'/'opr' before 'chrome' since those browsers also contain 'chrome').
  private static readonly BROWSER_MATCHERS: ReadonlyArray<readonly [string, string]> = [
    ['edge', 'Microsoft Edge'],
    ['edg', 'Chromium-based Edge'],
    ['opr', 'Opera'],
    ['chrome', 'Chrome'],
    ['trident', 'Internet Explorer'],
    ['firefox', 'Firefox'],
    ['safari', 'Safari']
  ];

  private getBrowserName(): string {
    for (const [token, name] of BrowserDetectorService.BROWSER_MATCHERS) {
      if (this.agent.includes(token)) {
        return name;
      }
    }
    return 'other';
  }

}
