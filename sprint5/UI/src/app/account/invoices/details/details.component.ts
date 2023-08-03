import {Component, OnInit} from '@angular/core';
import {Invoice} from "../../../models/invoice";
import {InvoiceService} from "../../../_services/invoice.service";
import {first} from "rxjs/operators";
import {ActivatedRoute} from "@angular/router";
import {HttpResponse} from "@angular/common/http";

@Component({
  selector: 'app-details',
  templateUrl: './details.component.html',
  styleUrls: ['./details.component.css']
})
export class DetailsComponent implements OnInit {
  invoice!: Invoice;
  error: string;
  hideAlert: boolean = false;

  constructor(private invoiceService: InvoiceService,
              private route: ActivatedRoute) {
  }

  ngOnInit(): void {
    this.invoiceService.getInvoice(this.route.snapshot.params["id"])
      .pipe(first())
      .subscribe((invoice) => this.invoice = invoice);
  }

  downloadPDF() {
    this.invoiceService.downloadPDF(this.invoice.invoice_number).pipe(first())
      .subscribe(
        (response: HttpResponse<Blob>) => {
          const file = new Blob([response.body], {type: 'application/pdf'});
          const fileURL = URL.createObjectURL(file);
          console.log(response);
          const contentDisposition = response.headers.get('Content-Disposition');
          const filename = contentDisposition.split(';')[1].split('filename')[1].split('=')[1].trim();

          const a = document.createElement('a');
          a.href = fileURL;
          a.target = '_blank';
          a.download = filename;
          document.body.appendChild(a);
          a.click();
        },
        (error) => {
          this.hideAlert = false;
          this.error = 'Document not created. Try again later.';
        });
  }

  fadeOutMessage(): any {
    setTimeout(() => {
      this.hideAlert = true;
    }, 3000);
  }
}
