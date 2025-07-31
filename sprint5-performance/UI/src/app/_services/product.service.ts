import {inject, Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {Observable, throwError} from "rxjs";
import {HttpClient, HttpErrorResponse, HttpParams} from "@angular/common/http";
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
    const params = new HttpParams().set('page', page.toString());
    return this.httpClient.get<Pagination<Product>>(this.apiURL, { params });
  }

  getProductsNew(searchQuery: string, sorting: string, minPrice: string, maxPrice: string, categoryIds: string, brandIds: string, page: number): Observable<Pagination<Product>> {
    let params = new HttpParams().set('page', page.toString());

    if (searchQuery) params = params.set('q', searchQuery);
    if (sorting) params = params.set('sort', sorting);
    if (minPrice && maxPrice) params = params.set('between', `price,${minPrice},${maxPrice}`);
    if (categoryIds) params = params.set('by_category', categoryIds);
    if (brandIds) params = params.set('by_brand', brandIds);

    return this.httpClient.get<Pagination<Product>>(this.apiURL, { params });
  }

  getProductRentals(): Observable<Pagination<Product>> {
    const params = new HttpParams().set('is_rental', 'true');
    return this.httpClient.get<Pagination<Product>>(this.apiURL, { params });
  }

  getProductsByCategory(slug: string, page: number): Observable<Pagination<Product>> {
    const params = new HttpParams()
      .set('page', page.toString())
      .set('by_category_slug', slug);
    return this.httpClient.get<Pagination<Product>>(this.apiURL, { params });
  }

  searchProducts(query: string): Observable<Pagination<Product>> {
    const params = new HttpParams().set('q', query);
    return this.httpClient.get<Pagination<Product>>(`${this.apiURL}/search`, { params });
  }

  getProduct(id: string): Observable<Product> {
    return this.httpClient.get<Product>(`${this.apiURL}/${id}`);
  }

  getRelatedProducts(id: string): Observable<Product[]> {
    return this.httpClient.get<Product[]>(`${this.apiURL}/${id}/related`);
  }

  getProductsByCategoryAndBrand(categoryIds: string, brandIds: string, sorting: string, slug?: string): Observable<Pagination<Product>> {
    let params = new HttpParams();

    if (categoryIds) params = params.set('by_category', categoryIds);
    if (brandIds) params = params.set('by_brand', brandIds);
    if (sorting) params = params.set('sort', sorting);
    if (slug) params = params.set('by_category_slug', slug);

    return this.httpClient.get<Pagination<Product>>(this.apiURL, { params });
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

  private errorHandler(error: HttpErrorResponse): Observable<never> {
    return throwError(() => error.error || "Server error.");
  }
}
