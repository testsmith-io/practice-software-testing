// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {inject, Injectable} from '@angular/core';
import {HttpClient, HttpErrorResponse, HttpParams} from '@angular/common/http';
import {Observable, throwError} from 'rxjs';
import {catchError, shareReplay} from 'rxjs/operators';
import {environment} from '../../environments/environment';
import {Category} from '../models/category';

@Injectable({
  providedIn: 'root'
})
export class CategoryService {
  private readonly httpClient = inject(HttpClient);
  private readonly apiURL = `${environment.apiUrl}/categories`;
  private categoriesTree$: Observable<Category[]> | null = null;
  private categories$: Observable<Category[]> | null = null;

  searchCategories(query: string): Observable<any> {
    const params = new HttpParams().set('q', query);

    return this.httpClient.get<any>(`${this.apiURL}/search`, { params })
      .pipe(catchError(this.handleError));
  }

  getCategoriesTree(): Observable<Category[]> {
    if (!this.categoriesTree$) {
      this.categoriesTree$ = this.httpClient.get<Category[]>(`${this.apiURL}/tree`)
        .pipe(
          shareReplay(1),
          catchError(this.handleError)
        );
    }
    return this.categoriesTree$;
  }

  getSubCategoriesTreeBySlug(slug: string): Observable<Category[]> {
    const params = new HttpParams().set('by_category_slug', slug);

    return this.httpClient.get<Category[]>(`${this.apiURL}/tree`, { params })
      .pipe(catchError(this.handleError));
  }

  getCategories(): Observable<Category[]> {
    if (!this.categories$) {
      this.categories$ = this.httpClient.get<Category[]>(this.apiURL)
        .pipe(
          shareReplay(1),
          catchError(this.handleError)
        );
    }
    return this.categories$;
  }

  invalidateCategoriesCache(): void {
    this.categoriesTree$ = null;
    this.categories$ = null;
  }

  getById(id: string): Observable<Category> {
    return this.httpClient.get<Category>(`${this.apiURL}/tree/${id}`)
      .pipe(catchError(this.handleError));
  }

  create(category: Category): Observable<Category> {
    this.invalidateCategoriesCache();
    return this.httpClient.post<Category>(this.apiURL, category)
      .pipe(catchError(this.handleError));
  }

  update(id: string, category: Category): Observable<Category> {
    this.invalidateCategoriesCache();
    return this.httpClient.put<Category>(`${this.apiURL}/${id}`, category)
      .pipe(catchError(this.handleError));
  }

  delete(id: number): Observable<void> {
    this.invalidateCategoriesCache();
    return this.httpClient.delete<void>(`${this.apiURL}/${id}`)
      .pipe(catchError(this.handleError));
  }

  private handleError = (error: HttpErrorResponse): Observable<never> => {
    console.error('CategoryService Error:', error);

    const errorMessage = error.error?.message ||
      error.message ||
      'An unexpected error occurred';

    return throwError(() => new Error(errorMessage));
  };
}
