import {Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {map, Observable, throwError} from "rxjs";
import {HttpClient, HttpErrorResponse, HttpParams} from "@angular/common/http";
import {Brand} from "../models/brand";
import {catchError} from "rxjs/operators";

@Injectable({
  providedIn: 'root'
})
export class BrandService {

  private apiURL = environment.apiUrl;

  constructor(private httpClient: HttpClient) {
  }

  searchBrands(query: string): Observable<any> {
    let params = new HttpParams()
      .set('q', query);

    return this.httpClient.get(this.apiURL + '/brands/search', {responseType: 'json', params: params});
  }

  getBrands(): Observable<Brand[]> {
    return this.httpClient.get<Brand[]>(this.apiURL + `/brands`)
      .pipe(map(this.extractData));
  }

  getById(id: string): Observable<Brand> {
    return this.httpClient.get<Brand>(this.apiURL + `/brands/${id}`);
  }

  create(brand: Brand): Observable<any> {
    return this.httpClient.post(this.apiURL + '/brands', JSON.stringify(brand), {responseType: 'json'})
      .pipe(
        catchError(this.errorHandler)
      )
  }

  update(id: string, brand: Brand) {
    return this.httpClient.put(this.apiURL + `/brands/${id}`, JSON.stringify(brand), {responseType: 'json'})
      .pipe(
        catchError(this.errorHandler)
      )
  }

  delete(id: number) {
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
