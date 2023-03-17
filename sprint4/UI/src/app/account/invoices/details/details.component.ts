import {Component, OnInit} from '@angular/core';
import {Invoice} from "../../../models/invoice";
import {InvoiceService} from "../../../_services/invoice.service";
import {first} from "rxjs/operators";
import {ActivatedRoute} from "@angular/router";

@Component({
  selector: 'app-details',
  templateUrl: './details.component.html',
  styleUrls: ['./details.component.css']
})
export class DetailsComponent implements OnInit {
  invoice!: Invoice;

  constructor(private invoiceService: InvoiceService,
              private route: ActivatedRoute) { }

  ngOnInit(): void {
    this.invoiceService.getInvoice(this.route.snapshot.params["id"])
      .pipe(first())
      .subscribe((invoice)=> this.invoice = invoice);
  }
}
