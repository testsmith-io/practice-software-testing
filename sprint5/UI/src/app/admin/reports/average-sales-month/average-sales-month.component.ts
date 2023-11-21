import {Component, OnInit} from '@angular/core';
import {ReportService} from "../../../_services/report.service";
import {ChartConfiguration, ChartType} from "chart.js";
import DataLabelsPlugin from "chartjs-plugin-datalabels";

@Component({
  selector: 'app-average-sales-month',
  templateUrl: './average-sales-month.component.html',
  styleUrls: ['./average-sales-month.component.css']
})
export class AverageSalesMonthComponent implements OnInit {

  public barChartOptions: ChartConfiguration['options'] = {
    responsive: true,
    aspectRatio: 3,
    // We use these empty structures as placeholders for dynamic theming.
    scales: {
      x: {},
      y: {},
    },
    plugins: {
      legend: {
        display: true,
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

  options = {
    legend: {
      display: true
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

  constructor(private reportService: ReportService) {
  }

  ngOnInit(): void {
    this.getData('2022')
  }

  selectYear(year: any) {
    this.getData(year.target.value)
  }

  getData(year: any) {
    this.reportService.getAverageSalesPerMonth(year).subscribe(res => {
      let labels = res.map((item) => {
        return item['month'];
      });
      let data = res.map((item) => {
        return item['average'];
      });
      let amount = res.map((item) => {
        return item['amount'];
      });

      this.data = {
        labels: labels,
        datasets: [
          {
            label: "Average sales",
            data: data,
            backgroundColor: 'rgba(255, 206, 86, 1)',
          },
          {
            label: "Amount of orders",
            data: amount,
            backgroundColor: 'rgba(75, 192, 192, 1)',
          }]
      };
    })
  }

}
