import {inject, Injectable} from '@angular/core';
import {HttpClient, HttpErrorResponse, HttpParams} from '@angular/common/http';
import {catchError, Observable, throwError} from 'rxjs';
import {environment} from "../../environments/environment";
import {User} from "../models/user.model";
import {Pagination} from "../models/pagination";

@Injectable({
  providedIn: 'root'
})
export class UserService {
  private readonly httpClient = inject(HttpClient);
  private readonly apiURL = environment.apiUrl;

  getUsers(page: number): Observable<Pagination<User>> {
    const params = new HttpParams().set('page', page.toString());
    return this.httpClient.get<Pagination<User>>(`${this.apiURL}/users`, { params });
  }

  searchUsers(page: number, query: string): Observable<any> {
    const params = new HttpParams()
      .set('page', page.toString())
      .set('q', query);

    return this.httpClient.get(`${this.apiURL}/users/search`, { params });
  }

  getById(id: string): Observable<User> {
    return this.httpClient.get<User>(`${this.apiURL}/users/${id}`);
  }

  create(user: User): Observable<any> {
    return this.httpClient.post(`${this.apiURL}/users/register`, user)
      .pipe(catchError(this.errorHandler));
  }

  update(id: string, user: User): Observable<any> {
    return this.httpClient.put(`${this.apiURL}/users/${id}`, user)
      .pipe(catchError(this.errorHandler));
  }

  delete(id: number): Observable<any> {
    return this.httpClient.delete(`${this.apiURL}/users/${id}`)
      .pipe(catchError(this.errorHandler));
  }

  private errorHandler(error: HttpErrorResponse): Observable<never> {
    return throwError(() => error.error || "server error.");
  }
}
