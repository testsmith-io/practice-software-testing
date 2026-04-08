// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Component, inject, OnInit} from '@angular/core';
import {ComparisonProduct, ComparisonService} from "../../_services/comparison.service";
import {RouterLink} from "@angular/router";
import {TranslocoDirective} from "@jsverse/transloco";
import {NgClass} from "@angular/common";
import {FaIconComponent} from "@fortawesome/angular-fontawesome";

@Component({
  selector: 'app-comparison',
  templateUrl: './comparison.component.html',
  styleUrls: ['./comparison.component.css'],
  imports: [
    RouterLink,
    TranslocoDirective,
    NgClass,
    FaIconComponent
  ]
})
export class ComparisonComponent implements OnInit {
  private comparisonService = inject(ComparisonService);

  products: ComparisonProduct[] = [];
  loading = true;

  ngOnInit(): void {
    this.comparisonService.getComparisonProducts().subscribe({
      next: (products) => {
        this.products = products;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
      }
    });
  }

  removeProduct(productId: string): void {
    this.comparisonService.toggle(productId);
    this.products = this.products.filter(p => p.id !== productId);
  }

  clearAll(): void {
    this.comparisonService.clear();
    this.products = [];
  }

  getCo2Class(rating: string): string {
    const classes: Record<string, string> = {
      'A': 'rating-a', 'B': 'rating-b', 'C': 'rating-c', 'D': 'rating-d', 'E': 'rating-e'
    };
    return classes[rating] || '';
  }
}
