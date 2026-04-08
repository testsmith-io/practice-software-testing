// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {inject, Injectable} from '@angular/core';
import {HttpClient} from "@angular/common/http";
import {Observable, map} from "rxjs";
import {environment} from "../../environments/environment";

@Injectable({
  providedIn: 'root'
})
export class GraphqlService {
  private readonly httpClient = inject(HttpClient);
  private readonly graphqlUrl = `${environment.apiUrl}/graphql`;

  query<T>(query: string, variables: Record<string, any> = {}): Observable<T> {
    return this.httpClient.post<{ data: T }>(this.graphqlUrl, {query, variables}).pipe(
      map(response => response.data)
    );
  }
}
