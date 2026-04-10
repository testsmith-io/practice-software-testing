// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {ChangeDetectionStrategy, ChangeDetectorRef, Component, inject, OnInit} from '@angular/core';
import {ComparisonProduct, ComparisonService} from "../../_services/comparison.service";
import {RouterLink} from "@angular/router";
import {TranslocoDirective} from "@jsverse/transloco";
import {NgClass} from "@angular/common";
import {FaIconComponent} from "@fortawesome/angular-fontawesome";
import {FormsModule} from "@angular/forms";

@Component({
  selector: 'app-comparison',
  templateUrl: './comparison.component.html',
  styleUrls: ['./comparison.component.css'],
  imports: [
    RouterLink,
    TranslocoDirective,
    NgClass,
    FaIconComponent,
    FormsModule
  ],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class ComparisonComponent implements OnInit {
  private comparisonService = inject(ComparisonService);
  private cdr = inject(ChangeDetectorRef);

  products: ComparisonProduct[] = [];
  loading = true;
  allSpecNames: string[] = [];
  showOnlyDifferences = false;

  ngOnInit(): void {
    this.comparisonService.getComparisonProducts().subscribe({
      next: (products) => {
        this.products = products;
        this.allSpecNames = this.collectSpecNames(products);
        this.loading = false;
        this.cdr.markForCheck();
      },
      error: () => {
        this.loading = false;
        this.cdr.markForCheck();
      }
    });
  }

  removeProduct(productId: string): void {
    this.comparisonService.toggle(productId);
    this.products = this.products.filter(p => p.id !== productId);
    this.allSpecNames = this.collectSpecNames(this.products);
  }

  clearAll(): void {
    this.comparisonService.clear();
    this.products = [];
    this.allSpecNames = [];
  }

  getCo2Class(rating: string): string {
    const classes: Record<string, string> = {
      'A': 'rating-a', 'B': 'rating-b', 'C': 'rating-c', 'D': 'rating-d', 'E': 'rating-e'
    };
    return classes[rating] || '';
  }

  getSpecValue(product: ComparisonProduct, specName: string): string {
    const spec = product.specs?.find(s => s.spec_name === specName);
    if (!spec) return '-';
    return spec.spec_unit ? `${spec.spec_value} ${spec.spec_unit}` : spec.spec_value;
  }

  isSpecDifferent(specName: string): boolean {
    const values = this.products.map(p => this.getSpecValue(p, specName));
    return new Set(values).size > 1;
  }

  shouldShowSpec(specName: string): boolean {
    if (!this.showOnlyDifferences) return true;
    return this.isSpecDifferent(specName);
  }

  private collectSpecNames(products: ComparisonProduct[]): string[] {
    const names = new Set<string>();
    products.forEach(p => {
      p.specs?.forEach(s => names.add(s.spec_name));
    });
    return Array.from(names).sort();
  }
}
