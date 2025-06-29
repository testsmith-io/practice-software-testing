import {Component, inject, OnInit} from '@angular/core';
import {InvoiceService} from "../../_services/invoice.service";
import {Invoice} from "../../models/invoice";
import {first} from "rxjs/operators";
import {Pagination} from "../../models/pagination";
import {RouterLink} from "@angular/router";
import {NgxPaginationModule} from "ngx-pagination";
import {DecimalPipe} from "@angular/common";

@Component({
  selector: 'app-invoices',
  templateUrl: './invoices.component.html',
  imports: [
    RouterLink,
    NgxPaginationModule,
    DecimalPipe
],
  styleUrls: []
})
export class InvoicesComponent implements OnInit {
  private readonly invoiceService = inject(InvoiceService);

  p: number = 1;
  results: Pagination<Invoice>;

  ngOnInit(): void {
    this.getInvoices();
  }

  getInvoices() {
    this.invoiceService.getInvoices(this.p)
      .pipe(first())
      .subscribe((invoices) => this.results = invoices);
  }

  handlePageChange(event: number): void {
    this.p = event;
    this.getInvoices();
  }
}
