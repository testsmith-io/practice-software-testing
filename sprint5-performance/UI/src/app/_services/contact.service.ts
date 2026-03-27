import {inject, Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {Observable, of, switchMap, throwError} from "rxjs";
import {HttpClient, HttpErrorResponse, HttpParams} from "@angular/common/http";
import {catchError} from "rxjs/operators";
import {ContactMessage} from "../models/contact-message";

@Injectable({
  providedIn: 'root'
})
export class ContactService {
  private readonly httpClient = inject(HttpClient);
  private readonly apiURL = `${environment.apiUrl}/messages`;
  private readonly jsonHeaders = { 'Content-Type': 'application/json' };

  getMessages(page: number): Observable<any> {
    const params = new HttpParams().set('page', page.toString());
    return this.httpClient.get(this.apiURL, { params });
  }

  getMessage(id: string): Observable<any> {
    return this.httpClient.get(`${this.apiURL}/${id}`);
  }

  addReply(contact: ContactMessage, id: string): Observable<any> {
    return this.httpClient.post(`${this.apiURL}/${id}/reply`, contact, {
      headers: this.jsonHeaders
    });
  }

  sendMessage(file: File | null, contact: ContactMessage): Observable<any> {
    return this.httpClient.post(this.apiURL, contact, {
      headers: this.jsonHeaders
    }).pipe(
      switchMap((response: any) => {
        if (file) {
          return this.uploadFile(response.id, file);
        }
        return of(response);
      }),
      catchError(this.errorHandler)
    );
  }

  updateStatus(id: number, status: string): Observable<any> {
    return this.httpClient.put(`${this.apiURL}/${id}/status`, { status }, {
      headers: this.jsonHeaders
    });
  }

  private uploadFile(messageId: string, file: File): Observable<any> {
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
