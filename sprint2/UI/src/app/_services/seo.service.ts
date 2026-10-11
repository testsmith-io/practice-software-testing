// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {DOCUMENT} from '@angular/common';
import {inject, Injectable} from '@angular/core';
import {Meta} from '@angular/platform-browser';

const SITE_ORIGIN = 'https://practicesoftwaretesting.com';
const DEFAULT_DESCRIPTION =
  'Practice Software Testing (Toolshop) is a free, modern web application for ' +
  'learning and practicing software testing and test automation.';

/**
 * Keeps the per-route SEO head tags in sync: meta description, the canonical
 * link, and the og:url / og:description tags. Titles are handled separately by
 * AppComponent via the Angular Title service.
 */
@Injectable({providedIn: 'root'})
export class SeoService {
  private readonly meta = inject(Meta);
  private readonly document = inject(DOCUMENT);

  /** Update the description + og:description meta tags for the current route. */
  setDescription(description?: string): void {
    const value = description?.trim() || DEFAULT_DESCRIPTION;
    this.meta.updateTag({name: 'description', content: value});
    this.meta.updateTag({property: 'og:description', content: value});
  }

  /** Point the canonical link and og:url at the current absolute path. */
  setCanonicalUrl(path: string): void {
    const url = this.absoluteUrl(path);
    let link = this.document.head.querySelector<HTMLLinkElement>('link[rel="canonical"]');
    if (!link) {
      link = this.document.createElement('link');
      link.setAttribute('rel', 'canonical');
      this.document.head.appendChild(link);
    }
    link.setAttribute('href', url);
    this.meta.updateTag({property: 'og:url', content: url});
  }

  private absoluteUrl(path: string): string {
    if (!path || path === '/') {
      return `${SITE_ORIGIN}/`;
    }
    return `${SITE_ORIGIN}${path.startsWith('/') ? '' : '/'}${path}`;
  }
}
