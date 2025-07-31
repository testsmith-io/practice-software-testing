import {inject, Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {Observable, of, switchMap, throwError} from "rxjs";
import {HttpClient, HttpErrorResponse, HttpHeaders, HttpParams} from "@angular/common/http";
import {catchError} from "rxjs/operators";
import {ContactMessage} from "../models/contact-message";

@Injectable({
  providedIn: 'root'
})
export class ContactService {
  private httpClient = inject(HttpClient);
  private apiURL = environment.apiUrl;

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

  sendMessage(file: File, contact: ContactMessage): Observable<any> {
    return this.httpClient.post(this.apiURL + '/messages', JSON.stringify(contact), {responseType: 'json'})
      .pipe(
        switchMap((searchText: any) => {
            if (file !== null) {
              const formData = new FormData();
              formData.append('file', file);
              const options: any = {
                headers: new HttpHeaders({
                  'Accept': `application/json`
                })
              }
              return this.httpClient.post(this.apiURL + `/messages/${searchText.id}/attach-file`, formData, options);
            }
            return of(null);
          }
        ),
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
