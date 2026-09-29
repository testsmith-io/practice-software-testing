// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {inject, Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {Observable, of, switchMap, throwError} from "rxjs";
import {HttpClient, HttpErrorResponse, HttpParams} from "@angular/common/http";
import {catchError} from "rxjs/operators";
import {ContactMessage} from "../models/contact-message";
import {Pagination} from "../models/pagination";

@Injectable({
  providedIn: 'root'
})
export class ContactService {
  private readonly httpClient = inject(HttpClient);
  private readonly apiURL = `${environment.apiUrl}/messages`;
  private readonly jsonHeaders = { 'Content-Type': 'application/json' };

  getMessages(page: number): Observable<Pagination<ContactMessage>> {
    const params = new HttpParams().set('page', page.toString());
    return this.httpClient.get<Pagination<ContactMessage>>(this.apiURL, { params });
  }

  getMessage(id: string): Observable<ContactMessage> {
    return this.httpClient.get<ContactMessage>(`${this.apiURL}/${id}`);
  }

  addReply(contact: ContactMessage, id: string): Observable<ContactMessage> {
    return this.httpClient.post<ContactMessage>(`${this.apiURL}/${id}/reply`, contact, {
      headers: this.jsonHeaders
    });
  }

  sendMessage(file: File | null, contact: ContactMessage): Observable<unknown> {
    return this.httpClient.post<ContactMessage>(this.apiURL, contact, {
      headers: this.jsonHeaders
    }).pipe(
      switchMap((response: ContactMessage) => {
        if (file) {
          return this.uploadFile(response.id, file);
        }
        return of(response);
      }),
      catchError(this.errorHandler)
    );
  }

  updateStatus(id: number, status: string): Observable<ContactMessage> {
    return this.httpClient.put<ContactMessage>(`${this.apiURL}/${id}/status`, { status }, {
      headers: this.jsonHeaders
    });
  }

  private uploadFile(messageId: number | string, file: File): Observable<unknown> {
    const formData = new FormData();
    formData.append('file', file);

    return this.httpClient.post(`${this.apiURL}/${messageId}/attach-file`, formData, {
      headers: { 'Accept': 'application/json' }
    });
  }

  private errorHandler(error: HttpErrorResponse): Observable<never> {
    return throwError(() => error.error || "Server error.");
  }
}
