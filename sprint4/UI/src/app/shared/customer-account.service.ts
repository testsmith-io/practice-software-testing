// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {inject, Injectable} from '@angular/core';
import {HttpClient, HttpErrorResponse} from "@angular/common/http";
import {Observable, of, Subject, throwError} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {JwtHelperService} from '@auth0/angular-jwt';
import {environment} from "../../environments/environment";
import {TokenStorageService} from "../_services/token-storage.service";
import {User} from "../models/user.model";
import {Token} from "../models/token.model";
import {ChangePassword} from "../models/change-password.model";
import {Profile} from "../models/profile";

export interface Credentials {
  email: string;
  password: string;
}

export interface ApiSuccessResponse {
  success?: boolean;
}

@Injectable({
  providedIn: 'root'
})
export class CustomerAccountService {
  private readonly http = inject(HttpClient);
  private readonly tokenStorage = inject(TokenStorageService);

  public authSub = new Subject<string>();
  apiURL = environment.apiUrl;
  login(payload: Credentials): Observable<Token> {
    return this.http.post<Token>(this.apiURL + '/users/login', payload)
      .pipe(
        map(res => {
          this.authSub.next('changed');
          return res
        }),
        catchError(this.errorHandler)
      );
  }

  refreshToken(): Observable<Token> {
    return this.http.get<Token>(this.apiURL + '/users/refresh')
      .pipe(
        map(res => {
          this.authSub.next('changed');
          return res
        }),
        catchError(this.errorHandler)
      );
  }

  forgetPassword(payload: { email: string }): Observable<Token> {
    return this.http.post<Token>(this.apiURL + '/users/forgot-password', payload)
      .pipe(
        map(res => {
          return res
        }),
        catchError(this.errorHandler)
      );
  }

  register(payload: User): Observable<User> {
    return this.http.post<User>(this.apiURL + '/users/register', payload)
      .pipe(
        map(res => res),
        catchError(this.errorHandler));
  }

  getDetails(): Observable<Profile> {
    return this.http.get<Profile>(this.apiURL + '/users/me')
      .pipe(
        map(res => res),
        catchError(this.errorHandler));
  }

  update(id: number, user: User): Observable<ApiSuccessResponse> {
    return this.http.put<ApiSuccessResponse>(this.apiURL + '/users/' + id, JSON.stringify(user), {responseType: 'json'})
      .pipe(
        catchError(this.errorHandler)
      )
  }

  updatePassword(id: number, password: ChangePassword): Observable<ApiSuccessResponse> {
    return this.http.post<ApiSuccessResponse>(this.apiURL + '/users/change-password', JSON.stringify(password), {responseType: 'json'})
      .pipe(
        catchError(this.errorHandler)
      )
  }

  errorHandler(error: HttpErrorResponse) {
    return throwError(() => error.error || "server error.");
  }

  redirectToAccount() {
    window.location.href = '/#/account';
    return true;
  }

  redirectToDashboard() {
    window.location.href = '/#/admin/dashboard';
    return true;
  }

  redirectToLogin() {
    window.location.href = '/#/auth/login';
    return true;
  }

  getRole() {
    if (this.isLoggedIn()) {
      const authToken = this.tokenStorage.getToken();
      const helper = new JwtHelperService();
      return helper.decodeToken(<string>authToken).role;
    } else {
      return '';
    }
  }

  logout() {
    this.tokenStorage.signOut();
  }

  public isAuthenticated(): Observable<boolean> {
    const authToken = this.tokenStorage.getToken();
    const helper = new JwtHelperService();
    if (helper.isTokenExpired(authToken)) {

      return this.refreshAccessToken()
        .pipe(
          map(response => {
            return !!response;
          }),
          catchError(() => {
            return of(false);
          })
        );

    }
    return of(true);
  }

  public refreshAccessToken(): Observable<boolean> {
    return this.refreshToken()
      .pipe(
        map((response: Token) => {
          this.tokenStorage.saveToken(response.access_token);
          return true;
        })
      );
  }

  isLoggedIn(): boolean {
    const authToken = this.tokenStorage.getToken();
    const helper = new JwtHelperService();
    if (authToken === null) {
      return false;
    } else return !helper.isTokenExpired(authToken);
  }
}
