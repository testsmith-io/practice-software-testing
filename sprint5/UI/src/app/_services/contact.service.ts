import {Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {Observable, throwError} from "rxjs";
import {HttpClient, HttpErrorResponse, HttpParams} from "@angular/common/http";
import {catchError} from "rxjs/operators";
import {ContactMessage} from "../models/contact-message";

@Injectable({
  providedIn: 'root'
})
export class ContactService {
  private apiURL = environment.apiUrl;

  constructor(private httpClient: HttpClient) {
  }

  getMessages(page: any): Observable<any> {
    let params = new HttpParams().set('page', page);

    return this.httpClient.get(this.apiURL + '/messages', {responseType: 'json', params: params});
  }

  getMessage(id: string): Observable<any> {
    return this.httpClient.get(this.apiURL + `/messages/${id}`, {responseType: 'json'});
  }

  addReply(contact: ContactMessage, id: string): Observable<any> {
    return this.httpClient.post(this.apiURL + `/messages/${id}/reply`, JSON.stringify(contact), {responseType: 'json'});
  }

  sendMessage(contact: ContactMessage): Observable<any> {
    return this.httpClient.post(this.apiURL + '/messages', JSON.stringify(contact), {responseType: 'json'})
      .pipe(
        catchError(this.errorHandler)
      )
  }

  updateStatus(id: number, status: string): Observable<any> {
    return this.httpClient.put(this.apiURL + `/messages/${id}/status`, {
      status: status
    }, {responseType: 'json'});
  }

  errorHandler(error: HttpErrorResponse) {
    return throwError(error.error || "server error.");
  }

}
