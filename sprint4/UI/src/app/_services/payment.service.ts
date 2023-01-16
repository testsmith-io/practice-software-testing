import {Injectable} from '@angular/core';
import {HttpClient} from "@angular/common/http";
import {Observable} from "rxjs";

@Injectable({
  providedIn: 'root'
})
export class PaymentService {

  constructor(private http: HttpClient) {
  }

  validate(api_url: any, payload: any): Observable<any> {
    return this.http.post(api_url, payload, {responseType: 'json'});
  }
}
