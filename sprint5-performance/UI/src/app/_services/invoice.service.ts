// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {inject, Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {HttpClient, HttpParams, HttpResponse} from "@angular/common/http";
import {Observable} from "rxjs";
import {Invoice} from "../models/invoice";
import {Pagination} from "../models/pagination";

export interface InvoicePdfStatus {
  status: string;
}

@Injectable({
  providedIn: 'root'
})
export class InvoiceService {
  private readonly httpClient = inject(HttpClient);
  private readonly apiUrl = `${environment.apiUrl}/invoices`;

  getInvoices(page: number): Observable<Pagination<Invoice>> {
    const params = new HttpParams().set('page', page.toString());
    return this.httpClient.get<Pagination<Invoice>>(this.apiUrl, { params });
  }

  searchInvoices(page: number, query: string): Observable<Pagination<Invoice>> {
    return this.httpClient.request<Pagination<Invoice>>('QUERY', `${this.apiUrl}/search`, {
      body: { page: page.toString(), q: query },
      headers: { 'Content-Type': 'application/json' },
    });
  }

  getNewInvoices(page: number): Observable<Pagination<Invoice>> {
    const params = new HttpParams()
      .set('page', page.toString())
      .set('in', 'status,AWAITING_FULFILLMENT');
    return this.httpClient.get<Pagination<Invoice>>(this.apiUrl, { params });
  }

  getInvoice(id: string): Observable<Invoice> {
    return this.httpClient.get<Invoice>(`${this.apiUrl}/${id}`);
  }

  downloadPDF(invoice_number: string): Observable<HttpResponse<Blob>> {
    return this.httpClient.get(`${this.apiUrl}/${invoice_number}/download-pdf`, {
      observe: 'response',
      responseType: 'blob'
    });
  }

  getInvoicePdfStatus(invoice_number: string): Observable<InvoicePdfStatus> {
    return this.httpClient.get<InvoicePdfStatus>(`${this.apiUrl}/${invoice_number}/download-pdf-status`);
  }

  createInvoice(payload: Record<string, unknown>): Observable<Invoice> {
    // Check if this is a guest checkout
    if (payload['guest_email']) {
      return this.httpClient.post<Invoice>(`${this.apiUrl}/guest`, payload);
    }
    return this.httpClient.post<Invoice>(this.apiUrl, payload);
  }

  updateStatus(id: string, status: string, status_message: string): Observable<Invoice> {
    return this.httpClient.put<Invoice>(`${this.apiUrl}/${id}/status`, {
      status,
      status_message
    });
  }
}
