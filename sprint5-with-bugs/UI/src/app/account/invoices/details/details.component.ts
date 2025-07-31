import {Component, inject, OnInit} from '@angular/core';
import {Invoice} from "../../../models/invoice";
import {InvoiceService} from "../../../_services/invoice.service";
import {first} from "rxjs/operators";
import {ActivatedRoute} from "@angular/router";
import {DecimalPipe} from "@angular/common";

@Component({
  selector: 'app-details',
  templateUrl: './details.component.html',
  imports: [
    DecimalPipe
  ],
  styleUrls: []
})
export class DetailsComponent implements OnInit {
  private readonly invoiceService = inject(InvoiceService);
  private readonly route = inject(ActivatedRoute);
  invoice!: Invoice;

  ngOnInit(): void {
    this.invoiceService.getInvoice(this.route.snapshot.params["id"])
      .pipe(first())
      .subscribe((invoice) => this.invoice = invoice);
  }
}
