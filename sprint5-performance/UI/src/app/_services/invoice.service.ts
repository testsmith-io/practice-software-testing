import {inject, Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {HttpClient, HttpParams} from "@angular/common/http";
import {Observable} from "rxjs";

@Injectable({
  providedIn: 'root'
})
export class InvoiceService {
  private readonly httpClient = inject(HttpClient);
  private readonly apiUrl = `${environment.apiUrl}/invoices`;

  getInvoices(page: number): Observable<any> {
    const params = new HttpParams().set('page', page.toString());
    return this.httpClient.get(this.apiUrl, { params });
  }

  searchInvoices(page: number, query: string): Observable<any> {
    const params = new HttpParams()
      .set('page', page.toString())
      .set('q', query);
    return this.httpClient.get(`${this.apiUrl}/search`, { params });
  }

  getNewInvoices(page: number): Observable<any> {
    const params = new HttpParams()
      .set('page', page.toString())
      .set('in', 'status,AWAITING_FULFILLMENT');
    return this.httpClient.get(this.apiUrl, { params });
  }

  getInvoice(id: string): Observable<any> {
    return this.httpClient.get(`${this.apiUrl}/${id}`);
  }

  downloadPDF(invoice_number: string): Observable<any> {
    return this.httpClient.get(`${this.apiUrl}/${invoice_number}/download-pdf`, {
      observe: 'response',
      responseType: 'blob'
    });
  }

  getInvoicePdfStatus(invoice_number: string): Observable<any> {
    return this.httpClient.get(`${this.apiUrl}/${invoice_number}/download-pdf-status`);
  }

  createInvoice(payload: any): Observable<any> {
    return this.httpClient.post(this.apiUrl, payload);
  }

  updateStatus(id: string, status: string, status_message: string): Observable<any> {
    return this.httpClient.put(`${this.apiUrl}/${id}/status`, {
      status,
      status_message
    });
  }
}
