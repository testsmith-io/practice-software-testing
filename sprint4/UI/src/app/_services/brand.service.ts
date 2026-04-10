// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {inject, Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {map, Observable, throwError} from "rxjs";
import {HttpClient, HttpErrorResponse, HttpParams} from "@angular/common/http";
import {Brand} from "../models/brand";
import {catchError, shareReplay} from "rxjs/operators";

@Injectable({
  providedIn: 'root'
})
export class BrandService {
  private httpClient = inject(HttpClient);
  private apiURL = environment.apiUrl;
  private brands$: Observable<Brand[]> | null = null;

  searchBrands(query: string): Observable<any> {
    let params = new HttpParams()
      .set('q', query);

    return this.httpClient.get(this.apiURL + '/brands/search', {responseType: 'json', params: params});
  }

  getBrands(): Observable<Brand[]> {
    if (!this.brands$) {
      this.brands$ = this.httpClient.get<Brand[]>(this.apiURL + `/brands`)
        .pipe(
          map(this.extractData),
          shareReplay(1)
        );
    }
    return this.brands$;
  }

  invalidateBrandsCache(): void {
    this.brands$ = null;
  }

  getById(id: string): Observable<Brand> {
    return this.httpClient.get<Brand>(this.apiURL + `/brands/${id}`);
  }

  create(brand: Brand): Observable<any> {
    this.invalidateBrandsCache();
    return this.httpClient.post(this.apiURL + '/brands', JSON.stringify(brand), {responseType: 'json'})
      .pipe(
        catchError(this.errorHandler)
      )
  }

  update(id: string, brand: Brand) {
    this.invalidateBrandsCache();
    return this.httpClient.put(this.apiURL + `/brands/${id}`, JSON.stringify(brand), {responseType: 'json'})
      .pipe(
        catchError(this.errorHandler)
      )
  }

  delete(id: number) {
    this.invalidateBrandsCache();
    return this.httpClient.delete(this.apiURL + `/brands/${id}`, {responseType: 'json'})
      .pipe(
        catchError(this.errorHandler)
      )
  }

  errorHandler(error: HttpErrorResponse) {
    return throwError(error.error || "server error.");
  }

  private extractData = (res: any) => res;

}
