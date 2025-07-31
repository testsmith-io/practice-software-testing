import {Component, inject, OnInit} from '@angular/core';
import {Invoice} from "../../models/invoice";
import {InvoiceService} from "../../_services/invoice.service";
import {ActivatedRoute, RouterLink} from "@angular/router";
import {first} from "rxjs/operators";
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from "@angular/forms";
import {OrderState} from "../../models/order-state";
import {DecimalPipe, NgClass} from "@angular/common";

@Component({
  selector: 'app-orders-add-edit',
  templateUrl: './orders-add-edit.component.html',
  imports: [
    RouterLink,
    ReactiveFormsModule,
    NgClass,
    DecimalPipe
  ],
  styleUrls: []
})
export class OrdersAddEditComponent implements OnInit {
  private readonly formBuilder = inject(FormBuilder);
  private readonly invoiceService = inject(InvoiceService);
  private readonly route = inject(ActivatedRoute);

  statuses = ["AWAITING_FULFILLMENT", "ON_HOLD", "AWAITING_SHIPMENT", "SHIPPED", "COMPLETED"];
  invoiceForm: FormGroup | any;
  invoice!: Invoice;
  isUpdated: boolean = false;
  hideAlert: boolean = false;
  error: string;
  orderState: any = OrderState;

  ngOnInit(): void {
    this.invoiceForm = this.formBuilder.group(
      {
        status: ['', [Validators.required]],
        status_message: ['', []]
      });

    this.invoiceService.getInvoice(this.route.snapshot.params["id"])
      .pipe(first())
      .subscribe((invoice) => {
        this.invoiceForm.controls['status'].setValue(invoice.status);
        this.invoiceForm.controls['status_message'].setValue(invoice.status_message);
        this.invoice = invoice
      });
  }

  updateStatus() {
    if (this.invoice.status == this.invoiceForm.controls['status'].value) {
      this.error = 'No new status selected.';
      return;
    }
    if (this.invoice.status == OrderState[OrderState.ON_HOLD] && !this.invoiceForm.controls['status_message'].value) {
      this.error = 'Order was "on hold", status message must be set.';
      return;
    }
    let status = this.invoiceForm.controls['status'].value;
    let status_message = this.invoiceForm.controls['status_message'].value;
    this.invoiceService.updateStatus(this.invoice.id, status, status_message).pipe(first())
      .subscribe({
        next: () => {
          this.isUpdated = true;
        }, error: (err) => {
          this.error = Object.values(err).join('\r\n');
        }, complete: () => {
          this.hideAlert = false;
        }
      });
  }

  fadeOutMessage(): any {
    setTimeout(() => {
      this.hideAlert = true;
    }, 3000);
  }
}
