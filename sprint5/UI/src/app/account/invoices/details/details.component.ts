import {Component, OnDestroy, OnInit} from '@angular/core';
import {Invoice} from "../../../models/invoice";
import {InvoiceService} from "../../../_services/invoice.service";
import {catchError, concatMap, first, startWith, tap} from "rxjs/operators";
import {ActivatedRoute} from "@angular/router";
import {HttpResponse} from "@angular/common/http";
import {interval, of, Subscription} from 'rxjs';
import {switchMap, takeWhile} from 'rxjs/operators';

@Component({
  selector: 'app-details',
  templateUrl: './details.component.html',
  styleUrls: ['./details.component.css']
})
export class DetailsComponent implements OnInit, OnDestroy {
  invoice!: Invoice;
  error: string;
  hideAlert: boolean = false;
  isDownloadReady = false;
  private pollingSubscription?: Subscription;

  constructor(private invoiceService: InvoiceService,
              private route: ActivatedRoute) {
  }

  ngOnInit(): void {
    this.invoiceService.getInvoice(this.route.snapshot.params["id"])
      .pipe(
        first(),
        tap((invoice) => this.invoice = invoice),
        concatMap(() => interval(20000).pipe(
          startWith(0),
          switchMap(() => this.invoiceService.getInvoicePdfStatus(this.invoice.invoice_number)
            .pipe(
              catchError((error) => {
                console.error('Error retrieving invoice PDF status:', error);
                return of({status: 'ERROR'});  // Return an error status or some default value
              })
            )
          ),
          takeWhile((response) => response.status !== 'COMPLETED', true) // Stop polling zodra de status COMPLETED is
        ))
      )
      .subscribe((response) => {
        if (response && response.status === 'COMPLETED') {
          this.isDownloadReady = true;
        }
      });
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
        () => {
          this.hideAlert = false;
          this.error = 'Document not created. Try again later.';
        });
  }

  fadeOutMessage(): any {
    setTimeout(() => {
      this.hideAlert = true;
    }, 3000);
  }

  ngOnDestroy() {
    // Zorg dat de subscription stopt als de component wordt vernietigd
    if (this.pollingSubscription) {
      this.pollingSubscription.unsubscribe();
    }
  }
}
