// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {inject, Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {HttpClient, HttpParams} from "@angular/common/http";
import {map, Observable} from "rxjs";
import {Invoice} from "../models/invoice";
import {Pagination} from "../models/pagination";

const API_URL = environment.apiUrl;

@Injectable({
  providedIn: 'root'
})
export class InvoiceService {
  private readonly httpClient = inject(HttpClient);

  getInvoices(page: number): Observable<Pagination<Invoice>> {
    const params = new HttpParams().set('page', page);

    return this.httpClient.get<Pagination<Invoice>>(API_URL + '/invoices', {responseType: 'json', params: params});
  }

  searchInvoices(page: number, query: string): Observable<Pagination<Invoice>> {
    const params = new HttpParams().set('page', page)
      .set('q', query);

    return this.httpClient.get<Pagination<Invoice>>(API_URL + '/invoices/search', {responseType: 'json', params: params});
  }

  getNewInvoices(page: number): Observable<Pagination<Invoice>> {
    const params = new HttpParams().set('page', page);

    return this.httpClient.get<Pagination<Invoice>>(API_URL + '/invoices?in=status,AWAITING_FULFILLMENT', {responseType: 'json', params: params})
      .pipe(map(this.extractData));
  }

  getInvoice(id: string): Observable<Invoice> {
    return this.httpClient.get<Invoice>(API_URL + `/invoices/${id}`, {responseType: 'json'});
  }

  createInvoice(payload: Record<string, unknown>): Observable<Invoice> {
    return this.httpClient.post<Invoice>(API_URL + '/invoices', payload, {responseType: 'json'});
  }

  updateStatus(id: number, status: string, status_message: string): Observable<Invoice> {
    return this.httpClient.put<Invoice>(API_URL + `/invoices/${id}/status`, {
      status: status,
      status_message: status_message
    }, {responseType: 'json'});
  }

  private extractData = <T>(res: T): T => res;
}
