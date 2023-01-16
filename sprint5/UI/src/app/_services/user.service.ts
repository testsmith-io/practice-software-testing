import {Injectable} from '@angular/core';
import {HttpClient, HttpErrorResponse, HttpParams} from '@angular/common/http';
import {map, Observable, throwError} from 'rxjs';
import {environment} from "../../environments/environment";
import {catchError} from "rxjs/operators";
import {User} from "../models/user.model";
import {Pagination} from "../models/pagination";

@Injectable({
  providedIn: 'root'
})
export class UserService {
  apiURL = environment.apiUrl;

  constructor(private httpClient: HttpClient) {
  }

  getUsers(page: any): Observable<Pagination<User>> {
    let params = new HttpParams().set('page', page);

    return this.httpClient.get<User[]>(environment.apiUrl + `/users`, {params: params})
      .pipe(map(this.extractData));
  }
  searchUsers(page:any, query: string): Observable<any> {
    let params = new HttpParams().set('page', page)
      .set('q', query);

    return this.httpClient.get(environment.apiUrl + '/users/search', {responseType: 'json', params: params});
  }

  getById(id: string): Observable<User> {
    return this.httpClient.get<User>(environment.apiUrl + `/users/${id}`);
  }

  create(user: User): Observable<any> {
    return this.httpClient.post(this.apiURL + '/users/register', JSON.stringify(user), {responseType: 'json'})
      .pipe(
        catchError(this.errorHandler)
      )
  }

  update(id: string, user: User) {
    return this.httpClient.put(this.apiURL + `/users/${id}`, JSON.stringify(user), {responseType: 'json'})
      .pipe(
        catchError(this.errorHandler)
      )
  }

  delete(id: number) {
    return this.httpClient.delete(this.apiURL + `/users/${id}`, {responseType: 'json'})
      .pipe(
        catchError(this.errorHandler)
      )
  }

  errorHandler(error: HttpErrorResponse) {
    return throwError(error.error || "server error.");
  }

  private extractData = (res: any) => res;
}
