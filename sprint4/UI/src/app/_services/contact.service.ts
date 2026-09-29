// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {inject, Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {Observable, throwError} from "rxjs";
import {HttpClient, HttpErrorResponse, HttpParams} from "@angular/common/http";
import {catchError} from "rxjs/operators";
import {ContactMessage} from "../models/contact-message";
import {Pagination} from "../models/pagination";

@Injectable({
  providedIn: 'root'
})
export class ContactService {
  private readonly httpClient = inject(HttpClient);
  private readonly apiURL = environment.apiUrl;

  getMessages(page: number): Observable<Pagination<ContactMessage>> {
    const params = new HttpParams().set('page', page);

    return this.httpClient.get<Pagination<ContactMessage>>(this.apiURL + '/messages', {responseType: 'json', params: params});
  }

  getMessage(id: string): Observable<ContactMessage> {
    return this.httpClient.get<ContactMessage>(this.apiURL + `/messages/${id}`, {responseType: 'json'});
  }

  addReply(contact: ContactMessage, id: string): Observable<ContactMessage> {
    return this.httpClient.post<ContactMessage>(this.apiURL + `/messages/${id}/reply`, JSON.stringify(contact), {responseType: 'json'});
  }

  sendMessage(contact: ContactMessage): Observable<ContactMessage> {
    return this.httpClient.post<ContactMessage>(this.apiURL + '/messages', JSON.stringify(contact), {responseType: 'json'})
      .pipe(
        catchError(this.errorHandler)
      )
  }

  updateStatus(id: number, status: string): Observable<ContactMessage> {
    return this.httpClient.put<ContactMessage>(this.apiURL + `/messages/${id}/status`, {
      status: status
    }, {responseType: 'json'});
  }

  errorHandler(error: HttpErrorResponse) {
    return throwError(() => error.error || "server error.");
  }

}
