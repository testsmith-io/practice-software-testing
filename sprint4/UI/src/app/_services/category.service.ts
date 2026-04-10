// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {inject, Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {map, Observable, throwError} from "rxjs";
import {HttpClient, HttpErrorResponse, HttpParams} from "@angular/common/http";
import {catchError, shareReplay} from "rxjs/operators";
import {Category} from "../models/category";

@Injectable({
  providedIn: 'root'
})
export class CategoryService {
  private httpClient = inject(HttpClient);
  private apiURL = environment.apiUrl;
  private categoriesTree$: Observable<Category[]> | null = null;
  private categories$: Observable<Category[]> | null = null;

  searchCategories(query: string): Observable<any> {
    let params = new HttpParams()
      .set('q', query);

    return this.httpClient.get(this.apiURL + '/categories/search', {responseType: 'json', params: params});
  }

  getCategoriesTree(): Observable<Category[]> {
    if (!this.categoriesTree$) {
      this.categoriesTree$ = this.httpClient.get<Category[]>(this.apiURL + `/categories/tree`)
        .pipe(
          map(this.extractData),
          shareReplay(1)
        );
    }
    return this.categoriesTree$;
  }

  getSubCategoriesTreeBySlug(slug: string): Observable<Category[]> {
    let params = new HttpParams().set('by_category_slug', slug);
    return this.httpClient.get(this.apiURL + `/categories/tree`, {params: params})
      .pipe(map(this.extractData));
  }

  getCategories(): Observable<Category[]> {
    if (!this.categories$) {
      this.categories$ = this.httpClient.get<Category[]>(this.apiURL + `/categories`)
        .pipe(
          map(this.extractData),
          shareReplay(1)
        );
    }
    return this.categories$;
  }

  invalidateCategoriesCache(): void {
    this.categories$ = null;
    this.categoriesTree$ = null;
  }

  getById(id: string): Observable<Category> {
    return this.httpClient.get<Category>(this.apiURL + `/categories/tree/${id}`);
  }

  create(category: Category): Observable<any> {
    this.invalidateCategoriesCache();
    return this.httpClient.post(this.apiURL + '/categories', JSON.stringify(category), {responseType: 'json'})
      .pipe(
        catchError(this.errorHandler)
      )
  }

  update(id: string, category: Category) {
    this.invalidateCategoriesCache();
    return this.httpClient.put(this.apiURL + `/categories/${id}`, JSON.stringify(category), {responseType: 'json'})
      .pipe(
        catchError(this.errorHandler)
      )
  }

  delete(id: number) {
    this.invalidateCategoriesCache();
    return this.httpClient.delete(this.apiURL + `/categories/${id}`, {responseType: 'json'})
      .pipe(
        catchError(this.errorHandler)
      )
  }

  errorHandler(error: HttpErrorResponse) {
    return throwError(error.error || "server error.");
  }

  private extractData = (res: any) => res;

}
