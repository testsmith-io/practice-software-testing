import {Injectable} from '@angular/core';

export interface BrowserInfo {
  name: string;
  version: string;
  isMobile: boolean;
}

export enum BrowserType {
  CHROME = 'Chrome',
  FIREFOX = 'Firefox',
  SAFARI = 'Safari',
  EDGE = 'Microsoft Edge',
  CHROMIUM_EDGE = 'Chromium-based Edge',
  OPERA = 'Opera',
  IE = 'Internet Explorer',
  OTHER = 'Other'
}

@Injectable({
  providedIn: 'root'
})
export class BrowserDetectorService {
  private readonly userAgent: string;
  private readonly browserInfo: BrowserInfo;

  // Regex patterns for better accuracy
  private readonly browserPatterns = {
    [BrowserType.EDGE]: /edg(?:e|ios|a)?\/([\d\.]+)/i,
    [BrowserType.CHROMIUM_EDGE]: /edg\/([\d\.]+)/i,
    [BrowserType.OPERA]: /(?:opr|opera)\/([\d\.]+)/i,
    [BrowserType.CHROME]: /chrome\/([\d\.]+)/i,
    [BrowserType.FIREFOX]: /firefox\/([\d\.]+)/i,
    [BrowserType.SAFARI]: /version\/([\d\.]+).*safari/i,
    [BrowserType.IE]: /(?:msie |trident.*rv:)([\d\.]+)/i
  };

  constructor() {
    this.userAgent = this.getUserAgent();
    this.browserInfo = this.detectBrowser();
  }

  // Public API methods
  isFirefox(): boolean {
    return this.browserInfo.name === BrowserType.FIREFOX;
  }

  isChrome(): boolean {
    return this.browserInfo.name === BrowserType.CHROME;
  }

  isSafari(): boolean {
    return this.browserInfo.name === BrowserType.SAFARI;
  }

  isEdge(): boolean {
    return this.browserInfo.name === BrowserType.EDGE ||
      this.browserInfo.name === BrowserType.CHROMIUM_EDGE;
  }

  isOpera(): boolean {
    return this.browserInfo.name === BrowserType.OPERA;
  }

  isInternetExplorer(): boolean {
    return this.browserInfo.name === BrowserType.IE;
  }

  getBrowserName(): string {
    return this.browserInfo.name;
  }

  getBrowserVersion(): string {
    return this.browserInfo.version;
  }

  isMobile(): boolean {
    return this.browserInfo.isMobile;
  }

  getBrowserInfo(): BrowserInfo {
    return { ...this.browserInfo };
  }

  // Check for specific features
  supportsWebGL(): boolean {
    try {
      const canvas = document.createElement('canvas');
      return !!(canvas.getContext('webgl') || canvas.getContext('experimental-webgl'));
    } catch {
      return false;
    }
  }

  supportsLocalStorage(): boolean {
    try {
      return typeof Storage !== 'undefined';
    } catch {
      return false;
    }
  }

  // Private methods
  private getUserAgent(): string {
    if (typeof window === 'undefined' || !window.navigator) {
      return ''; // SSR compatibility
    }
    return window.navigator.userAgent.toLowerCase();
  }

  private detectBrowser(): BrowserInfo {
    const name = this.detectBrowserName();
    const version = this.detectBrowserVersion(name);
    const isMobile = this.detectMobile();

    return { name, version, isMobile };
  }

  private detectBrowserName(): string {
    // Order matters - check more specific patterns first
    const checkOrder: (keyof typeof this.browserPatterns)[] = [
      BrowserType.EDGE,
      BrowserType.CHROMIUM_EDGE,
      BrowserType.OPERA,
      BrowserType.CHROME,
      BrowserType.IE,
      BrowserType.FIREFOX,
      BrowserType.SAFARI
    ];

    for (const browser of checkOrder) {
      if (this.browserPatterns[browser].test(this.userAgent)) {
        return browser;
      }
    }

    return BrowserType.OTHER;
  }

  private detectBrowserVersion(browserName: string): string {
    if (browserName === BrowserType.OTHER || !(browserName in this.browserPatterns)) {
      return '';
    }

    const pattern = this.browserPatterns[browserName as keyof typeof this.browserPatterns];
    const match = this.userAgent.match(pattern);

    if (match && match[1]) {
      return match[1].split('.')[0]; // Return major version only
    }

    return '';
  }

  private detectMobile(): boolean {
    if (typeof window === 'undefined' || !window.navigator) {
      return false;
    }

    // Multiple detection methods for better accuracy
    const touchPoints = window.navigator.maxTouchPoints > 0;
    const mobileUserAgent = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(this.userAgent);
    const smallScreen = window.screen && window.screen.width < 768;

    return touchPoints || mobileUserAgent || smallScreen;
  }
}
