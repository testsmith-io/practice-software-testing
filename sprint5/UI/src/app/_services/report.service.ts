import {inject, Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {Observable} from "rxjs";
import {HttpClient} from "@angular/common/http";

@Injectable({
  providedIn: 'root'
})
export class ReportService {
  private readonly httpClient = inject(HttpClient);
  private readonly apiURL = `${environment.apiUrl}/reports`;

  getTotalSalesPerYear(): Observable<any[]> {
    return this.httpClient.get<any[]>(`${this.apiURL}/total-sales-of-years?years=5`);
  }

  getAverageSalesPerMonth(year: string): Observable<any[]> {
    return this.httpClient.get<any[]>(`${this.apiURL}/average-sales-per-month?year=${year}`);
  }

  getAverageSalesPerWeek(year: string): Observable<any[]> {
    return this.httpClient.get<any[]>(`${this.apiURL}/average-sales-per-week?year=${year}`);
  }

  getTotalSalesPerCountry(): Observable<any[]> {
    return this.httpClient.get<any[]>(`${this.apiURL}/total-sales-per-country`);
  }

  getTop10PurchachedProducts(): Observable<any[]> {
    return this.httpClient.get<any[]>(`${this.apiURL}/top10-purchased-products`);
  }

  getTop10BestSellingCategories(): Observable<any[]> {
    return this.httpClient.get<any[]>(`${this.apiURL}/top10-best-selling-categories`);
  }

  getCustomerByCountry(): Observable<any[]> {
    return this.httpClient.get<any[]>(`${this.apiURL}/customers-by-country`);
  }
}
