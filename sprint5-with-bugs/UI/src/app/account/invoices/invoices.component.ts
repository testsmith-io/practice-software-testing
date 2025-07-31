import {Component, inject, OnInit} from '@angular/core';
import {InvoiceService} from "../../_services/invoice.service";
import {Invoice} from "../../models/invoice";
import {first} from "rxjs/operators";
import {Pagination} from "../../models/pagination";
import {CustomerAccountService} from "../../shared/customer-account.service";
import {NgxPaginationModule} from "ngx-pagination";
import {DecimalPipe} from "@angular/common";
import {RouterLink} from "@angular/router";

@Component({
  selector: 'app-invoices',
  templateUrl: './invoices.component.html',
  imports: [
    NgxPaginationModule,
    DecimalPipe,
    RouterLink
  ],
  styleUrls: []
})
export class InvoicesComponent implements OnInit {
  private readonly invoiceService = inject(InvoiceService);
  private readonly customerAccountService = inject(CustomerAccountService);
  p: number = 1;
  results: Pagination<Invoice>;
  protected id: any;

  ngOnInit(): void {
    this.customerAccountService.getDetails()
      .pipe(first())
      .subscribe((profile) => {
          this.id = profile.id;
        },
        (error) => {
          if (error.status === 401 || error.status === 403) {
            window.localStorage.removeItem('TOKEN_KEY');
            window.location.href = '/#/auth/login';
          }
        });

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
