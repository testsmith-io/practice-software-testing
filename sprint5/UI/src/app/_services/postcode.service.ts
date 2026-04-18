// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {inject, Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable} from 'rxjs';
import {environment} from '../../environments/environment';

export interface PostcodeLookupResult {
  street: string;
  city: string;
  state: string;
  country: string;
  postcode: string;
}

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
    return this.http.get<PostcodeLookupResult>(this.apiURL, {params});
  }
}
