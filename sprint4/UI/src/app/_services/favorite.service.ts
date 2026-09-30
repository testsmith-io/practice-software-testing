// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {inject, Injectable} from '@angular/core';
import {HttpClient} from "@angular/common/http";
import {Observable} from "rxjs";
import {environment} from "../../environments/environment";
import {Favorite} from "../models/favorite";

const API_URL = environment.apiUrl;

@Injectable({
  providedIn: 'root'
})
export class FavoriteService {
  private readonly httpClient = inject(HttpClient);

  addFavorite(payload: Record<string, unknown>): Observable<Favorite> {
    return this.httpClient.post<Favorite>(API_URL + '/favorites', payload, {responseType: 'json'});
  }

  getFavorites(): Observable<Favorite[]> {
    return this.httpClient.get<Favorite[]>(API_URL + '/favorites', {responseType: 'json'});
  }

  deleteFavorite(id: number): Observable<unknown> {
    return this.httpClient.delete<unknown>(`${API_URL}/favorites/${id}`, {responseType: 'json'});
  }
}
