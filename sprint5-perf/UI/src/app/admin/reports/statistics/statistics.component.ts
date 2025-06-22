import {Component, OnInit} from '@angular/core';
import {ReportService} from "../../../_services/report.service";

@Component({
  selector: 'app-statistics',
  templateUrl: './statistics.component.html',
  styleUrls: ['./statistics.component.css']
})
export class StatisticsComponent implements OnInit {
  top10BestSellingCategories: any;
  top10PurchasedProducts: any;
  customerByCountry: any;
  totalSalesPerCountry: any;

  constructor(private reportService: ReportService) { }

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
