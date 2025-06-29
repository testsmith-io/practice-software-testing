import {inject, Injectable} from '@angular/core';
import {HttpClient, HttpErrorResponse, HttpParams} from '@angular/common/http';
import {Observable, throwError} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {environment} from '../../environments/environment';
import {Brand} from '../models/brand';

@Injectable({
  providedIn: 'root'
})
export class BrandService {
  private readonly httpClient = inject(HttpClient);
  private readonly apiURL = `${environment.apiUrl}/brands`;

  searchBrands(query: string): Observable<Brand[]> {
    const params = new HttpParams().set('q', query);
    return this.httpClient.get<Brand[]>(`${this.apiURL}/search`, { params })
      .pipe(catchError(this.handleError));
  }

  getBrands(): Observable<Brand[]> {
    return this.httpClient.get<Brand[]>(this.apiURL)
      .pipe(catchError(this.handleError));
  }

  getById(id: string): Observable<Brand> {
    return this.httpClient.get<Brand>(`${this.apiURL}/${id}`)
      .pipe(catchError(this.handleError));
  }

  create(brand: Brand): Observable<Brand> {
    return this.httpClient.post<Brand>(this.apiURL, brand)
      .pipe(catchError(this.handleError));
  }

  update(id: string, brand: Brand): Observable<Brand> {
    return this.httpClient.put<Brand>(`${this.apiURL}/${id}`, brand)
      .pipe(catchError(this.handleError));
  }

  delete(id: string): Observable<void> {
    return this.httpClient.delete<void>(`${this.apiURL}/${id}`)
      .pipe(catchError(this.handleError));
  }

  private handleError = (error: HttpErrorResponse): Observable<never> => {
    console.error('BrandService Error:', error);

    // Return user-friendly error message
    const errorMessage = error.error?.message ||
      error.message ||
      'An unexpected error occurred';

    return throwError(() => new Error(errorMessage));
  };
}
