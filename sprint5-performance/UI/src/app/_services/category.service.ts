import {inject, Injectable} from '@angular/core';
import {HttpClient, HttpErrorResponse, HttpParams} from '@angular/common/http';
import {Observable, throwError} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {environment} from '../../environments/environment';
import {Category} from '../models/category';

@Injectable({
  providedIn: 'root'
})
export class CategoryService {
  private readonly httpClient = inject(HttpClient);
  private readonly apiURL = `${environment.apiUrl}/categories`;

  searchCategories(query: string): Observable<any> {
    const params = new HttpParams().set('q', query);

    return this.httpClient.get<any>(`${this.apiURL}/search`, { params })
      .pipe(catchError(this.handleError));
  }

  getCategoriesTree(): Observable<Category[]> {
    return this.httpClient.get<Category[]>(`${this.apiURL}/tree`)
      .pipe(catchError(this.handleError));
  }

  getSubCategoriesTreeBySlug(slug: string): Observable<Category[]> {
    const params = new HttpParams().set('by_category_slug', slug);

    return this.httpClient.get<Category[]>(`${this.apiURL}/tree`, { params })
      .pipe(catchError(this.handleError));
  }

  getCategories(): Observable<Category[]> {
    return this.httpClient.get<Category[]>(this.apiURL)
      .pipe(catchError(this.handleError));
  }

  getById(id: string): Observable<Category> {
    return this.httpClient.get<Category>(`${this.apiURL}/tree/${id}`)
      .pipe(catchError(this.handleError));
  }

  create(category: Category): Observable<Category> {
    return this.httpClient.post<Category>(this.apiURL, category)
      .pipe(catchError(this.handleError));
  }

  update(id: string, category: Category): Observable<Category> {
    return this.httpClient.put<Category>(`${this.apiURL}/${id}`, category)
      .pipe(catchError(this.handleError));
  }

  delete(id: number): Observable<void> {
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
