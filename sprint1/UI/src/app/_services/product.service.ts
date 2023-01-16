import {Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {map, Observable, throwError} from "rxjs";
import {HttpClient, HttpErrorResponse, HttpParams} from "@angular/common/http";
import {catchError} from "rxjs/operators";
import {Product} from "../models/product";

@Injectable({
  providedIn: 'root'
})
export class ProductService {
  private apiURL = environment.apiUrl;

  constructor(private httpClient: HttpClient) {
  }

  getProducts(): Observable<Product[]> {
    return this.httpClient.get(this.apiURL + `/products`)
      .pipe(map(this.extractData));
  }

  getProductsByCategory(slug: string): Observable<Product[]> {
    let params = new HttpParams()
      .set('by_category_slug', slug);
    return this.httpClient.get(this.apiURL + `/products`, {params: params})
      .pipe(map(this.extractData));
  }

  searchProducts(query: string): Observable<Product[]> {
    let params = new HttpParams().set('q', query);

    return this.httpClient.get(this.apiURL + `/products/search`, {params: params})
      .pipe(map(this.extractData));
  }

  getProduct(id: number): Observable<any> {
    return this.httpClient.get(this.apiURL + `/products/${id}`);
  }

  getRelatedProducts(id: number): Observable<any> {
    return this.httpClient.get(this.apiURL + `/products/${id}/related`);
  }

  getProductsByCategoryAndBrand(categoryIds: any, brandIds: any, sorting: string, slug?: string): Observable<Product[]> {
    let params = new HttpParams();
    if (categoryIds.length) {
      params = params.set('by_category', categoryIds);
    }
    if (brandIds.length) {
      params = params.set('by_brand', brandIds);
    }
    if (sorting) {
      params = params.set('sort', sorting);
    }
    if (slug) {
      params = params.set('by_category_slug', slug);
    }
    return this.httpClient.get(this.apiURL + '/products', {params: params})
      .pipe(map(this.extractData));
  }

  getById(id: string): Observable<Product> {
    return this.httpClient.get<Product>(environment.apiUrl + `/products/${id}`);
  }

  create(category: Product): Observable<any> {
    return this.httpClient.post(this.apiURL + '/products', JSON.stringify(category), {responseType: 'json'})
      .pipe(
        catchError(this.errorHandler)
      )
  }

  update(id: string, category: Product) {
    return this.httpClient.put(this.apiURL + `/products/${id}`, JSON.stringify(category), {responseType: 'json'})
      .pipe(
        catchError(this.errorHandler)
      )
  }

  delete(id: number) {
    return this.httpClient.delete(this.apiURL + `/products/${id}`, {responseType: 'json'})
      .pipe(
        catchError(this.errorHandler)
      )
  }

  errorHandler(error: HttpErrorResponse) {
    return throwError(error.error || "server error.");
  }

  private extractData = (res: any) => res;

}
