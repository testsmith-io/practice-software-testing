import {Component, OnInit} from '@angular/core';
import {ReportService} from "../../_services/report.service";
import {Invoice} from "../../models/invoice";
import {InvoiceService} from "../../_services/invoice.service";
import {first} from "rxjs/operators";
import {Pagination} from "../../models/pagination";
import { ChartConfiguration, ChartType } from 'chart.js';
import DataLabelsPlugin from 'chartjs-plugin-datalabels';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnInit {

  public barChartOptions: ChartConfiguration['options'] = {
    responsive: true,
    aspectRatio: 4,
    // We use these empty structures as placeholders for dynamic theming.
    scales: {
      x: {},
      y: {
        min: 10,
      },
    },
    plugins: {
      legend: {
        display: false,
      },
      datalabels: {
        anchor: 'end',
        align: 'end',
      },
    },
  };
  public barChartType: ChartType = 'bar';
  public barChartPlugins = [DataLabelsPlugin];


  type = 'bar';
  data: any;
  currentPage: number = 1;
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
    this.invoiceService.getNewInvoices(this.currentPage)
      .pipe(first())
      .subscribe((invoices)=> this.results = invoices);
  }

  onPageChange(page: number) {
    // Handle page change here (e.g., fetch data for the selected page)
    this.currentPage = page;
    this.getNewInvoices();
  }

}
