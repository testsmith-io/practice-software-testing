import {Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {map, Observable} from "rxjs";
import {HttpClient} from "@angular/common/http";
import {Image} from "../models/image";

@Injectable({
  providedIn: 'root'
})
export class ReportService {
  private apiURL = environment.apiUrl;

  constructor(private httpClient: HttpClient) {
  }

  getTotalSalesPerYear(): Observable<any[]> {
    return this.httpClient.get<Image[]>(this.apiURL + `/reports/total-sales-of-years?years=5`)
      .pipe(map(this.extractData));
  }

  getAverageSalesPerMonth(year: string): Observable<any[]> {
    return this.httpClient.get<Image[]>(this.apiURL + `/reports/average-sales-per-month?year=${year}`)
      .pipe(map(this.extractData));
  }

  getAverageSalesPerWeek(year: string): Observable<any[]> {
    return this.httpClient.get<Image[]>(this.apiURL + `/reports/average-sales-per-week?year=${year}`)
      .pipe(map(this.extractData));
  }

  getTotalSalesPerCountry(): Observable<any[]> {
    return this.httpClient.get<Image[]>(this.apiURL + `/reports/total-sales-per-country`)
      .pipe(map(this.extractData));
  }

  getTop10PurchachedProducts(): Observable<any[]> {
    return this.httpClient.get<Image[]>(this.apiURL + `/reports/top10-purchased-products`)
      .pipe(map(this.extractData));
  }

  getTop10BestSellingCategories(): Observable<any[]> {
    return this.httpClient.get<Image[]>(this.apiURL + `/reports/top10-best-selling-categories`)
      .pipe(map(this.extractData));
  }

  getCustomerByCountry(): Observable<any[]> {
    return this.httpClient.get<Image[]>(this.apiURL + `/reports/customers-by-country`)
      .pipe(map(this.extractData));
  }

  private extractData = (res: any) => res;

}
