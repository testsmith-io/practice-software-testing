import {Component, inject, OnInit} from '@angular/core';
import {ReportService} from "../../../_services/report.service";
import {RenderDelayDirective} from "../../../render-delay-directive.directive";

@Component({
  selector: 'app-statistics',
  templateUrl: './statistics.component.html',
  imports: [
    RenderDelayDirective
  ],
  styleUrls: []
})
export class StatisticsComponent implements OnInit {
  private readonly reportService = inject(ReportService);

  top10BestSellingCategories: any;
  top10PurchasedProducts: any;
  customerByCountry: any;
  totalSalesPerCountry: any;

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
