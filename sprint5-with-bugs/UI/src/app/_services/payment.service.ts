import {inject, Injectable} from '@angular/core';
import {HttpClient} from "@angular/common/http";
import {Observable} from "rxjs";

@Injectable({
  providedIn: 'root'
})
export class PaymentService {
  private httpClient = inject(HttpClient);

  validate(api_url: any, payload: any): Observable<any> {
    return this.httpClient.post(api_url, payload, {responseType: 'json'});
  }
}
