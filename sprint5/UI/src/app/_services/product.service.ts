// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {inject, Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {Observable, throwError} from "rxjs";
import {HttpClient, HttpErrorResponse} from "@angular/common/http";
import {catchError} from "rxjs/operators";
import {Product} from "../models/product";
import {Pagination} from "../models/pagination";

@Injectable({
  providedIn: 'root'
})
export class ProductService {
  private readonly httpClient = inject(HttpClient);
  private readonly apiURL = `${environment.apiUrl}/products`;
  private readonly jsonHeaders = { 'Content-Type': 'application/json' };

  getProducts(page: number): Observable<Pagination<Product>> {
    return this.query<Pagination<Product>>(this.apiURL, { page: page.toString() });
  }

  getProductsNew(searchQuery: string, sorting: string, minPrice: string, maxPrice: string, categoryIds: string, brandIds: string, page: number, ecoFriendly: boolean = false, isRental: boolean | null = false, specFilter: string = ''): Observable<Pagination<Product>> {
    const criteria: Record<string, string> = { page: page.toString() };

    if (searchQuery) criteria['q'] = searchQuery;
    if (sorting) criteria['sort'] = sorting;
    if (minPrice && maxPrice) criteria['between'] = `price,${minPrice},${maxPrice}`;
    if (categoryIds) criteria['by_category'] = categoryIds;
    if (brandIds) criteria['by_brand'] = brandIds;
    if (ecoFriendly) criteria['eco_friendly'] = 'true';
    if (isRental !== null) criteria['is_rental'] = isRental ? 'true' : 'false';
    if (specFilter) criteria['by_spec'] = specFilter;

    return this.query<Pagination<Product>>(this.apiURL, criteria);
  }

  getProductRentals(): Observable<Pagination<Product>> {
    return this.query<Pagination<Product>>(this.apiURL, { is_rental: 'true' });
  }

  getProductsByCategory(slug: string, page: number): Observable<Pagination<Product>> {
    return this.query<Pagination<Product>>(this.apiURL, {
      page: page.toString(),
      by_category_slug: slug,
    });
  }

  searchProducts(query: string): Observable<Pagination<Product>> {
    return this.query<Pagination<Product>>(`${this.apiURL}/search`, { q: query });
  }

  getProduct(id: string): Observable<Product> {
    return this.httpClient.get<Product>(`${this.apiURL}/${id}`);
  }

  getRelatedProducts(id: string): Observable<Product[]> {
    return this.httpClient.get<Product[]>(`${this.apiURL}/${id}/related`);
  }

  getProductsByCategoryAndBrand(categoryIds: string, brandIds: string, sorting: string, slug?: string, ecoFriendly: boolean = false, specFilter: string = ''): Observable<Pagination<Product>> {
    const criteria: Record<string, string> = {};

    if (categoryIds) criteria['by_category'] = categoryIds;
    if (brandIds) criteria['by_brand'] = brandIds;
    if (sorting) criteria['sort'] = sorting;
    if (slug) criteria['by_category_slug'] = slug;
    if (ecoFriendly) criteria['eco_friendly'] = 'true';
    if (specFilter) criteria['by_spec'] = specFilter;

    return this.query<Pagination<Product>>(this.apiURL, criteria);
  }

  getById(id: string): Observable<Product> {
    return this.httpClient.get<Product>(`${this.apiURL}/${id}`);
  }

  create(product: Product): Observable<Product> {
    return this.httpClient.post<Product>(this.apiURL, product, { headers: this.jsonHeaders })
      .pipe(catchError(this.errorHandler));
  }

  update(id: string, product: Product): Observable<Product> {
    return this.httpClient.put<Product>(`${this.apiURL}/${id}`, product, { headers: this.jsonHeaders })
      .pipe(catchError(this.errorHandler));
  }

  delete(id: string): Observable<void> {
    return this.httpClient.delete<void>(`${this.apiURL}/${id}`)
      .pipe(catchError(this.errorHandler));
  }

  // HTTP QUERY (RFC 10008): safe, idempotent request whose JSON body carries
  // the query criteria that would otherwise be sent as a query string.
  private query<T>(url: string, criteria: Record<string, string>): Observable<T> {
    return this.httpClient.request<T>('QUERY', url, { body: criteria, headers: this.jsonHeaders });
  }

  private errorHandler(error: HttpErrorResponse): Observable<never> {
    return throwError(() => error.error || "Server error.");
  }
}
