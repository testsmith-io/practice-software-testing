import {Component, inject, OnInit} from '@angular/core';
import {InvoiceService} from "../../_services/invoice.service";
import {Invoice} from "../../models/invoice";
import {first} from "rxjs/operators";
import {Pagination} from "../../models/pagination";
import {NavigationService} from "../../_services/navigation.service";
import {RenderDelayDirective} from "../../render-delay-directive.directive";
import {PaginationComponent} from "../../pagination/pagination.component";
import {RouterLink} from "@angular/router";
import {TranslocoDirective} from "@jsverse/transloco";

@Component({
  selector: 'app-invoices',
  templateUrl: './invoices.component.html',
  imports: [
    RenderDelayDirective,
    PaginationComponent,
    RouterLink,
    TranslocoDirective
  ],
  styleUrls: []
})
export class InvoicesComponent implements OnInit {
  private readonly invoiceService = inject(InvoiceService);
  private readonly navigationService = inject(NavigationService);

  currentPage: number = 1;
  results: Pagination<Invoice>;

  ngOnInit(): void {
    this.getInvoices();
  }

  getInvoices() {
    this.invoiceService.getInvoices(this.currentPage)
      .pipe(first())
      .subscribe(
        (invoices) => {
          this.results = invoices;
        },
        (error) => {
          if (error.status === 401 || error.status === 403) {
            this.navigationService.redirectToLogin();
          }
        }
      );
  }

  onPageChange(page: number) {
    // Handle page change here (e.g., fetch data for the selected page)
    this.currentPage = page;
    this.getInvoices();
  }

}
