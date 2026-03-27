import {Component, inject, OnInit} from '@angular/core';
import {ReportService} from "../../_services/report.service";
import {Invoice} from "../../models/invoice";
import {InvoiceService} from "../../_services/invoice.service";
import {first} from "rxjs/operators";
import {Pagination} from "../../models/pagination";
import {RouterLink} from "@angular/router";
import {BaseChartDirective} from "ng2-charts";
import DataLabelsPlugin from "chartjs-plugin-datalabels";
import {
  BarController,
  BarElement,
  CategoryScale,
  Chart,
  ChartConfiguration,
  ChartType,
  Legend,
  LinearScale,
  Tooltip
} from "chart.js";
import {NgxPaginationModule} from "ngx-pagination";

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  imports: [
    BaseChartDirective,
    RouterLink,
    NgxPaginationModule,
  ],
  styleUrls: []
})
export class DashboardComponent implements OnInit {
  private readonly invoiceService = inject(InvoiceService);
  private readonly reportService = inject(ReportService);

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

  ngOnInit(): void {
    Chart.register(
      CategoryScale,
      LinearScale,
      BarElement,
      BarController,
      Tooltip,
      Legend,
      DataLabelsPlugin
    );
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
