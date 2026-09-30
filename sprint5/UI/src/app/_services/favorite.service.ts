// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {inject, Injectable} from '@angular/core';
import {HttpClient} from "@angular/common/http";
import {Observable} from "rxjs";
import {environment} from "../../environments/environment";
import {Favorite} from "../models/favorite";

@Injectable({
  providedIn: 'root'
})
export class FavoriteService {
  private readonly httpClient = inject(HttpClient);
  private readonly apiUrl = `${environment.apiUrl}/favorites`;

  addFavorite(payload: { product_id: string }): Observable<Favorite> {
    return this.httpClient.post<Favorite>(this.apiUrl, payload);
  }

  getFavorites(): Observable<Favorite[]> {
    return this.httpClient.get<Favorite[]>(this.apiUrl);
  }

  deleteFavorite(id: string): Observable<void> {
    return this.httpClient.delete<void>(`${this.apiUrl}/${id}`);
  }
}
