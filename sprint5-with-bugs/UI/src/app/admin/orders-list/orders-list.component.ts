import {Component, OnInit} from '@angular/core';
import {first} from "rxjs/operators";
import {InvoiceService} from "../../_services/invoice.service";
import {Invoice} from "../../models/invoice";
import {Pagination} from "../../models/pagination";
import {FormBuilder, FormGroup, Validators} from "@angular/forms";

@Component({
  selector: 'app-list',
  templateUrl: './orders-list.component.html',
  styleUrls: ['./orders-list.component.css']
})
export class OrdersListComponent implements OnInit {

  p: number = 1;
  results: Pagination<Invoice>;
  searchForm: FormGroup | any;

  constructor(private invoiceService: InvoiceService,
              private formBuilder: FormBuilder) {
  }

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
