// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {inject, Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {map, Observable} from "rxjs";
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
  private readonly apiURL = environment.apiUrl;

  getTotalSalesPerYear(): Observable<SalesPerYear[]> {
    return this.httpClient.get<SalesPerYear[]>(this.apiURL + `/reports/total-sales-of-years?years=5`)
      .pipe(map(this.extractData));
  }

  getAverageSalesPerMonth(year: string): Observable<AverageSalesPerMonth[]> {
    return this.httpClient.get<AverageSalesPerMonth[]>(this.apiURL + `/reports/average-sales-per-month?year=${year}`)
      .pipe(map(this.extractData));
  }

  getAverageSalesPerWeek(year: string): Observable<AverageSalesPerWeek[]> {
    return this.httpClient.get<AverageSalesPerWeek[]>(this.apiURL + `/reports/average-sales-per-week?year=${year}`)
      .pipe(map(this.extractData));
  }

  getTotalSalesPerCountry(): Observable<SalesPerCountry[]> {
    return this.httpClient.get<SalesPerCountry[]>(this.apiURL + `/reports/total-sales-per-country`)
      .pipe(map(this.extractData));
  }

  getTop10PurchachedProducts(): Observable<TopPurchasedProduct[]> {
    return this.httpClient.get<TopPurchasedProduct[]>(this.apiURL + `/reports/top10-purchased-products`)
      .pipe(map(this.extractData));
  }

  getTop10BestSellingCategories(): Observable<TopSellingCategory[]> {
    return this.httpClient.get<TopSellingCategory[]>(this.apiURL + `/reports/top10-best-selling-categories`)
      .pipe(map(this.extractData));
  }

  getCustomerByCountry(): Observable<CustomerPerCountry[]> {
    return this.httpClient.get<CustomerPerCountry[]>(this.apiURL + `/reports/customers-by-country`)
      .pipe(map(this.extractData));
  }

  private extractData = <T>(res: T): T => res;

}
