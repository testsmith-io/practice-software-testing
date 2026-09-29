// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Component, inject, OnInit} from '@angular/core';
import {ReportService} from "../../../_services/report.service";
import {CustomerPerCountry, SalesPerCountry, TopPurchasedProduct, TopSellingCategory} from "../../../models/report";

@Component({
  selector: 'app-statistics',
  templateUrl: './statistics.component.html',
  styleUrls: []
})
export class StatisticsComponent implements OnInit {
  private readonly reportService = inject(ReportService);

  top10BestSellingCategories: TopSellingCategory[] = [];
  top10PurchasedProducts: TopPurchasedProduct[] = [];
  customerByCountry: CustomerPerCountry[] = [];
  totalSalesPerCountry: SalesPerCountry[] = [];

  ngOnInit(): void {
    this.reportService.getTop10BestSellingCategories().subscribe(res => {
      this.top10BestSellingCategories = res;
    })

    this.reportService.getTop10PurchachedProducts().subscribe(res => {
      this.top10PurchasedProducts = res;
    })

    this.reportService.getCustomerByCountry().subscribe(res => {
      this.customerByCountry = res;
    })

    this.reportService.getTotalSalesPerCountry().subscribe(res => {
      this.totalSalesPerCountry = res;
    })
  }

}
