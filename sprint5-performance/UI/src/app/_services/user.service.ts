// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

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

  searchUsers(page: number, query: string): Observable<Pagination<User>> {
    return this.httpClient.request<Pagination<User>>('QUERY', `${this.apiURL}/users/search`, {
      body: { page: page.toString(), q: query },
      headers: { 'Content-Type': 'application/json' },
    });
  }

  getById(id: string): Observable<User> {
    return this.httpClient.get<User>(`${this.apiURL}/users/${id}`);
  }

  create(user: User): Observable<User> {
    return this.httpClient.post<User>(`${this.apiURL}/users/register`, user)
      .pipe(catchError(this.errorHandler));
  }

  update(id: string, user: User): Observable<User> {
    return this.httpClient.put<User>(`${this.apiURL}/users/${id}`, user)
      .pipe(catchError(this.errorHandler));
  }

  delete(id: number): Observable<void> {
    return this.httpClient.delete<void>(`${this.apiURL}/users/${id}`)
      .pipe(catchError(this.errorHandler));
  }

  private errorHandler(error: HttpErrorResponse): Observable<never> {
    return throwError(() => error.error || "server error.");
  }
}
