import {Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {HttpClient, HttpParams} from "@angular/common/http";
import {map, Observable} from "rxjs";

const API_URL = environment.apiUrl;

@Injectable({
  providedIn: 'root'
})
export class InvoiceService {

  constructor(private http: HttpClient) {
  }

  getInvoices(page:any): Observable<any> {
    let params = new HttpParams().set('page', page);

    return this.http.get(API_URL + '/invoices', {responseType: 'json', params: params});
  }

  searchInvoices(page:any, query: string): Observable<any> {
    let params = new HttpParams().set('page', page)
      .set('q', query);

    return this.http.get(API_URL + '/invoices/search', {responseType: 'json', params: params});
  }

  getNewInvoices(page:any): Observable<any> {
    let params = new HttpParams().set('page', page);

    return this.http.get(API_URL + '/invoices?in=status,AWAITING_FULFILLMENT', {responseType: 'json', params: params})
      .pipe(map(this.extractData));
  }

  getInvoice(id: string): Observable<any> {
    return this.http.get(API_URL + `/invoices/${id}`, {responseType: 'json'});
  }

  createInvoice(payload: any): Observable<any> {
    return this.http.post(API_URL + '/invoices', payload, {responseType: 'json'});
  }

  updateStatus(id: number, status: string, status_message: string): Observable<any> {
    return this.http.put(API_URL + `/invoices/${id}/status`, {
      status: status,
      status_message: status_message
    }, {responseType: 'json'});
  }

  private extractData = (res: any) => res;
}
