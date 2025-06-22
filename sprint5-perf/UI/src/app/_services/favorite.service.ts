import { Injectable } from '@angular/core';
import { HttpClient } from "@angular/common/http";
import { Observable } from "rxjs";
import { environment } from "../../environments/environment";

@Injectable({
  providedIn: 'root'
})
export class FavoriteService {
  private readonly apiUrl = `${environment.apiUrl}/favorites`;

  constructor(private http: HttpClient) {}

  addFavorite(payload: any): Observable<any> {
    return this.http.post(this.apiUrl, payload);
  }

  getFavorites(): Observable<any> {
    return this.http.get(this.apiUrl);
  }

  deleteFavorite(id: string): Observable<any> {
    return this.http.delete(`${this.apiUrl}/${id}`);
  }
}
