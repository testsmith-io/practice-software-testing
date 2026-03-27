import {inject, Injectable} from '@angular/core';
import {HttpClient} from "@angular/common/http";
import {Observable} from "rxjs";
import {environment} from "../../environments/environment";

@Injectable({
  providedIn: 'root'
})
export class FavoriteService {
  private readonly httpClient = inject(HttpClient);
  private readonly apiUrl = `${environment.apiUrl}/favorites`;

  addFavorite(payload: any): Observable<any> {
    return this.httpClient.post(this.apiUrl, payload);
  }

  getFavorites(): Observable<any> {
    return this.httpClient.get(this.apiUrl);
  }

  deleteFavorite(id: string): Observable<any> {
    return this.httpClient.delete(`${this.apiUrl}/${id}`);
  }
}
