// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {inject, Injectable} from '@angular/core';
import {BehaviorSubject, Observable, map} from "rxjs";
import {GraphqlService} from "./graphql.service";

export interface ComparisonProductSpec {
  id: string;
  spec_name: string;
  spec_value: string;
  spec_unit: string | null;
}

export interface ComparisonProduct {
  id: string;
  name: string;
  description: string;
  price: number;
  in_stock: boolean;
  is_rental: boolean;
  co2_rating: string;
  is_eco_friendly: boolean;
  brand: { id: string; name: string };
  category: { id: string; name: string };
  product_image: { file_name: string; by_name: string; by_url: string };
  specs: ComparisonProductSpec[];
}

@Injectable({
  providedIn: 'root'
})
export class ComparisonService {
  private readonly graphqlService = inject(GraphqlService);
  private readonly MAX_ITEMS = 4;

  private selectedIds = new BehaviorSubject<string[]>(this.loadFromStorage());
  selectedIds$ = this.selectedIds.asObservable();
  count$ = this.selectedIds$.pipe(map(ids => ids.length));

  toggle(productId: string): void {
    const current = this.selectedIds.value;
    if (current.includes(productId)) {
      this.selectedIds.next(current.filter(id => id !== productId));
    } else if (current.length < this.MAX_ITEMS) {
      this.selectedIds.next([...current, productId]);
    }
    this.saveToStorage();
  }

  isSelected(productId: string): boolean {
    return this.selectedIds.value.includes(productId);
  }

  clear(): void {
    this.selectedIds.next([]);
    this.saveToStorage();
  }

  getComparisonProducts(): Observable<ComparisonProduct[]> {
    const ids = this.selectedIds.value;
    if (ids.length === 0) {
      return new Observable(subscriber => {
        subscriber.next([]);
        subscriber.complete();
      });
    }

    const fragments = ids.map((id, i) =>
      `p${i}: product(id: "${id}") {
        id
        name
        description
        price
        in_stock
        is_rental
        co2_rating
        is_eco_friendly
        brand { id name }
        category { id name }
        product_image { file_name by_name by_url }
        specs { id spec_name spec_value spec_unit }
      }`
    ).join('\n');

    const query = `{ ${fragments} }`;

    return this.graphqlService.query<Record<string, ComparisonProduct>>(query).pipe(
      map(data => Object.values(data))
    );
  }

  private loadFromStorage(): string[] {
    try {
      return JSON.parse(sessionStorage.getItem('compare_ids') || '[]');
    } catch {
      return [];
    }
  }

  private saveToStorage(): void {
    sessionStorage.setItem('compare_ids', JSON.stringify(this.selectedIds.value));
  }
}
