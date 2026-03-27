import {inject, Injectable} from '@angular/core';
import {HttpClient} from "@angular/common/http";
import {Observable} from "rxjs";
import {environment} from "../../environments/environment";

const API_URL = environment.apiUrl;

@Injectable({
  providedIn: 'root'
})
export class FavoriteService {
  private httpClient = inject(HttpClient);

  addFavorite(payload: any): Observable<any> {
    return this.httpClient.post(API_URL + '/favorites', payload, {responseType: 'json'});
  }

  getFavorites(): Observable<any> {
    return this.httpClient.get(API_URL + '/favorites', {responseType: 'json'});
  }

  deleteFavorite(id: number): Observable<any> {
    return this.httpClient.delete(`${API_URL}/favorites/${id}`, {responseType: 'json'});
  }
}
