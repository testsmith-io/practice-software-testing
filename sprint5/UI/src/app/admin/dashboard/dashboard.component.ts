import {Component, OnInit} from '@angular/core';
import {ReportService} from "../../_services/report.service";
import {Invoice} from "../../models/invoice";
import {InvoiceService} from "../../_services/invoice.service";
import {first} from "rxjs/operators";
import {Pagination} from "../../models/pagination";

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnInit {

  type = 'bar';
  data: any;
  p: number = 1;
  results: Pagination<Invoice>;

  options = {
    legend: {
      display: false
    },
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      yAxes: [{
        ticks: {
          beginAtZero: true
        }
      }]
    }
  };

  constructor(private invoiceService: InvoiceService,
              private reportService: ReportService) {
  }

  ngOnInit(): void {
   this.getNewInvoices();

    this.reportService.getTotalSalesPerYear().subscribe(res => {
      let labels = res.map((item) => {
        return item['year'];
      });
      let data = res.map((item) => {
        return item['total'];
      });

      this.data = {
        labels: labels,
        datasets: [
          {
            label: "",
            data: data,
            backgroundColor: () => {
              let r = Math.floor(Math.random() * 255);
              let g = Math.floor(Math.random() * 255);
              let b = Math.floor(Math.random() * 255);
              return "rgba(" + r + "," + g + "," + b + ", 0.5)";
            },
          }]
      };
    })
  }

  getNewInvoices() {
    this.invoiceService.getNewInvoices(this.p)
      .pipe(first())
      .subscribe((invoices)=> this.results = invoices);
  }

  handlePageChange(event: number): void {
    this.p = event;
    this.getNewInvoices();
  }

}
