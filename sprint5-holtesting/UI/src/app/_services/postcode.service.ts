// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {inject, Injectable} from '@angular/core';
import {HttpClient, HttpHeaders, HttpParams} from '@angular/common/http';
import {Observable} from 'rxjs';
import {environment} from '../../environments/environment';

export interface PostcodeLookupResult {
  street: string;
  house_number: string;
  city: string;
  state: string;
  country: string;
  postcode: string;
}

export const POSTCODE_LOOKUP_URL_KEY = 'POSTCODE_LOOKUP_URL';

@Injectable({providedIn: 'root'})
export class PostcodeService {
  private readonly http = inject(HttpClient);
  private readonly apiURL = `${environment.apiUrl}/postcode-lookup`;

  lookup(country: string, postcode: string, houseNumber?: string | null): Observable<PostcodeLookupResult> {
    let params = new HttpParams()
      .set('country', country)
      .set('postcode', postcode);
    if (houseNumber) {
      params = params.set('house_number', houseNumber);
    }

    // Local-only runtime override: the admin Settings page stores a mock URL in
    // localStorage. The backend only honors the header when APP_ENV != production.
    let headers = new HttpHeaders();
    const overrideUrl = !environment.production
      ? window.localStorage.getItem(POSTCODE_LOOKUP_URL_KEY)
      : null;
    if (overrideUrl) {
      headers = headers.set('X-Postcode-Lookup-Url', overrideUrl);
    }

    return this.http.get<PostcodeLookupResult>(this.apiURL, {params, headers});
  }
}
