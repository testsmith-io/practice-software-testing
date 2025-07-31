import {Component, inject, OnDestroy, OnInit} from '@angular/core';
import {Invoice} from "../../../models/invoice";
import {InvoiceService} from "../../../_services/invoice.service";
import {catchError, concatMap, first, startWith, switchMap, takeWhile, tap} from "rxjs/operators";
import {ActivatedRoute} from "@angular/router";
import {HttpResponse} from "@angular/common/http";
import {interval, of, Subscription} from 'rxjs';
import {TranslocoDirective, TranslocoService} from "@jsverse/transloco";
import {Title} from "@angular/platform-browser";
import {DecimalPipe, KeyValuePipe, NgClass} from "@angular/common";
import {ReplaceUnderscoresPipe} from "../../../shared/replaceunderscores.pipe";
import {TitleCasePipe} from "../../../shared/titlecase.pipe";

@Component({
  selector: 'app-details',
  templateUrl: './details.component.html',
  imports: [
    NgClass,
    KeyValuePipe,
    ReplaceUnderscoresPipe,
    TitleCasePipe,
    DecimalPipe,
    TranslocoDirective,
  ],
  styleUrls: ['./details.component.css']
})
export class DetailsComponent implements OnInit, OnDestroy {
  private readonly invoiceService = inject(InvoiceService);
  private readonly route = inject(ActivatedRoute);
  private readonly titleService = inject(Title);
  private readonly translocoService = inject(TranslocoService);

  invoice!: Invoice;
  error: string;
  hideAlert: boolean = false;
  isDownloadReady = false;
  private pollingSubscription?: Subscription;

  ngOnInit(): void {
    this.invoiceService.getInvoice(this.route.snapshot.params["id"])
      .pipe(
        first(),
        tap((invoice) => {
          this.invoice = invoice;
          this.updateTitle(this.invoice.invoice_number);
        }),
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
          takeWhile((response) => response.status !== 'COMPLETED', true)
        ))
      )
      .subscribe((response) => {
        if (response && response.status === 'COMPLETED') {
          this.isDownloadReady = true;
        }
      });
  }

  downloadPDF() {
    this.invoiceService.downloadPDF(this.invoice.invoice_number).pipe(first()).subscribe({
      next: (response: HttpResponse<Blob>) => {
        const file = new Blob([response.body], { type: 'application/pdf' });
        const fileURL = URL.createObjectURL(file);
        const contentDisposition = response.headers.get('Content-Disposition');
        const filename = contentDisposition
          ?.split(';')[1]
          ?.split('filename')[1]
          ?.split('=')[1]
          ?.trim()
          ?.replace(/["']/g, '') || 'invoice.pdf';

        const a = document.createElement('a');
        a.href = fileURL;
        a.target = '_blank';
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        a.remove(); // Clean up
      },
      error: () => {
        this.hideAlert = false;
        this.error = 'Document not created. Try again later.';
      }
    });
  }

  fadeOutMessage(): any {
    setTimeout(() => {
      this.hideAlert = true;
    }, 3000);
  }

  ngOnDestroy() {
    if (this.pollingSubscription) {
      this.pollingSubscription.unsubscribe();
    }
  }

  getPaymentMethodTranslation(paymentMethod: string): string {
    const translationKey = `pages.checkout.payment.options.${paymentMethod}`;
    return this.translocoService.translate(translationKey);
  }

  private updateTitle(invoiceNumber: string) {
    this.titleService.setTitle(`Invoice: ${invoiceNumber} - Practice Software Testing - Toolshop - v5.0`);
  }

}
