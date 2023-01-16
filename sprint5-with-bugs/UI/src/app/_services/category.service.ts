import {Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {map, Observable, throwError} from "rxjs";
import {HttpClient, HttpErrorResponse, HttpParams} from "@angular/common/http";
import {catchError} from "rxjs/operators";
import {Category} from "../models/category";

@Injectable({
  providedIn: 'root'
})
export class CategoryService {
  private apiURL = environment.apiUrl;

  constructor(private httpClient: HttpClient) {
  }

  searchCategories(query: string): Observable<any> {
    let params = new HttpParams()
      .set('q', query);

    return this.httpClient.get(this.apiURL + '/categories/search', {responseType: 'json', params: params});
  }

  getCategoriesTree(): Observable<Category[]> {
    return this.httpClient.get(this.apiURL + `/categories/tree`)
      .pipe(map(this.extractData));
  }

  getSubCategoriesTreeBySlug(slug: string): Observable<Category[]> {
    let params = new HttpParams().set('by_category_slug', slug);
    return this.httpClient.get(this.apiURL + `/categories/tree`, {params: params})
      .pipe(map(this.extractData));
  }

  getCategories(): Observable<Category[]> {
    return this.httpClient.get<Category[]>(this.apiURL + `/categories`)
      .pipe(map(this.extractData));
  }

  getById(id: string): Observable<Category> {
    return this.httpClient.get<Category>(this.apiURL + `/categories/${id}`);
  }

  create(category: Category): Observable<any> {
    return this.httpClient.post(this.apiURL + '/categories', JSON.stringify(category), {responseType: 'json'})
      .pipe(
        catchError(this.errorHandler)
      )
  }

  update(id: string, category: Category) {
    return this.httpClient.put(this.apiURL + `/categories/${id}`, JSON.stringify(category), {responseType: 'json'})
      .pipe(
        catchError(this.errorHandler)
      )
  }

  delete(id: number) {
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
