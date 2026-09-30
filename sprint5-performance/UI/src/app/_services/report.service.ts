// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {inject, Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {Observable} from "rxjs";
import {HttpClient} from "@angular/common/http";
import {
  AverageSalesPerMonth,
  AverageSalesPerWeek,
  CustomerPerCountry,
  SalesPerCountry,
  SalesPerYear,
  TopPurchasedProduct,
  TopSellingCategory
} from "../models/report";

@Injectable({
  providedIn: 'root'
})
export class ReportService {
  private readonly httpClient = inject(HttpClient);
  private readonly apiURL = `${environment.apiUrl}/reports`;

  getTotalSalesPerYear(): Observable<SalesPerYear[]> {
    return this.httpClient.get<SalesPerYear[]>(`${this.apiURL}/total-sales-of-years?years=5`);
  }

  getAverageSalesPerMonth(year: string): Observable<AverageSalesPerMonth[]> {
    return this.httpClient.get<AverageSalesPerMonth[]>(`${this.apiURL}/average-sales-per-month?year=${year}`);
  }

  getAverageSalesPerWeek(year: string): Observable<AverageSalesPerWeek[]> {
    return this.httpClient.get<AverageSalesPerWeek[]>(`${this.apiURL}/average-sales-per-week?year=${year}`);
  }

  getTotalSalesPerCountry(): Observable<SalesPerCountry[]> {
    return this.httpClient.get<SalesPerCountry[]>(`${this.apiURL}/total-sales-per-country`);
  }

  getTop10PurchachedProducts(): Observable<TopPurchasedProduct[]> {
    return this.httpClient.get<TopPurchasedProduct[]>(`${this.apiURL}/top10-purchased-products`);
  }

  getTop10BestSellingCategories(): Observable<TopSellingCategory[]> {
    return this.httpClient.get<TopSellingCategory[]>(`${this.apiURL}/top10-best-selling-categories`);
  }

  getCustomerByCountry(): Observable<CustomerPerCountry[]> {
    return this.httpClient.get<CustomerPerCountry[]>(`${this.apiURL}/customers-by-country`);
  }
}
