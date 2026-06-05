// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {inject, Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {HttpClient} from "@angular/common/http";
import {Observable} from "rxjs";

export interface ProductSpec {
  id?: string;
  product_id?: string;
  spec_name: string;
  spec_value: string;
  spec_unit?: string;
}

export interface SpecNameGroup {
  name: string;
  values: string[];
  unit: string | null;
}

@Injectable({
  providedIn: 'root'
})
export class ProductSpecService {
  private readonly httpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  getSpecs(productId: string): Observable<ProductSpec[]> {
    return this.httpClient.get<ProductSpec[]>(`${this.apiUrl}/products/${productId}/specs`);
  }

  createSpec(productId: string, spec: Partial<ProductSpec>): Observable<ProductSpec> {
    return this.httpClient.post<ProductSpec>(`${this.apiUrl}/products/${productId}/specs`, spec);
  }

  updateSpec(productId: string, specId: string, spec: Partial<ProductSpec>): Observable<any> {
    return this.httpClient.put(`${this.apiUrl}/products/${productId}/specs/${specId}`, spec);
  }

  deleteSpec(productId: string, specId: string): Observable<any> {
    return this.httpClient.delete(`${this.apiUrl}/products/${productId}/specs/${specId}`);
  }

  getSpecNames(): Observable<SpecNameGroup[]> {
    return this.httpClient.get<SpecNameGroup[]>(`${this.apiUrl}/product-specs/names`);
  }
}
