import {Component, OnInit} from '@angular/core';
import {InvoiceService} from "../../_services/invoice.service";
import {Invoice} from "../../models/invoice";
import {first} from "rxjs/operators";
import {Pagination} from "../../models/pagination";

@Component({
  selector: 'app-invoices',
  templateUrl: './invoices.component.html',
  styleUrls: ['./invoices.component.css']
})
export class InvoicesComponent implements OnInit {
  p: number = 1;
  results: Pagination<Invoice>;

  constructor(private invoiceService: InvoiceService) {
  }

  ngOnInit(): void {
    this.getInvoices();
  }

  getInvoices() {
    this.invoiceService.getInvoices(this.p)
      .pipe(first())
      .subscribe((invoices) => {
          this.results = invoices
        },
        (error) => {
          if (error.status === 401 || error.status === 403) {
            window.localStorage.removeItem('TOKEN_KEY');
            window.location.href = '/#/auth/login';
          }
        });
  }

  handlePageChange(event: number): void {
    this.p = event;
    this.getInvoices();
  }
}
