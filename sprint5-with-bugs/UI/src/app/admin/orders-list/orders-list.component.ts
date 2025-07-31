import {Component, inject, OnInit} from '@angular/core';
import {first} from "rxjs/operators";
import {InvoiceService} from "../../_services/invoice.service";
import {Invoice} from "../../models/invoice";
import {Pagination} from "../../models/pagination";
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from "@angular/forms";
import {NgxPaginationModule} from "ngx-pagination";
import {RouterLink} from "@angular/router";

@Component({
  selector: 'app-list',
  templateUrl: './orders-list.component.html',
  imports: [
    ReactiveFormsModule,
    NgxPaginationModule,
    RouterLink
  ],
  styleUrls: []
})
export class OrdersListComponent implements OnInit {
  private readonly invoiceService = inject(InvoiceService);
  private readonly formBuilder = inject(FormBuilder);

  p: number = 1;
  results: Pagination<Invoice>;
  searchForm: FormGroup | any;

  ngOnInit(): void {
    this.getInvoices();

    this.searchForm = this.formBuilder.group(
      {
        query: ['', [Validators.required]]
      });
  }

  search() {
    let query = this.searchForm.controls['query'].value;
    this.invoiceService.searchInvoices(0, query)
      .pipe(first())
      .subscribe((invoices) => this.results = invoices);
  }

  reset() {
    this.p  = 0;
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
