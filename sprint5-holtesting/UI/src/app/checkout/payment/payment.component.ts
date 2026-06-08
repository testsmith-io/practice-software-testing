// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Component, inject, Input, OnDestroy, OnInit} from '@angular/core';
import {
  AbstractControl,
  FormBuilder,
  FormGroup,
  ReactiveFormsModule,
  ValidationErrors,
  Validators
} from "@angular/forms";
import {CartService} from "../../_services/cart.service";
import {Observable, of} from "rxjs";
import {catchError, map} from "rxjs/operators";
import {environment} from "../../../environments/environment";
import {PaymentService} from "../../_services/payment.service";
import {InvoiceService} from "../../_services/invoice.service";
import {TranslocoDirective} from "@jsverse/transloco";
import {GaService} from "../../_services/ga.service";
import {CustomerAccountService} from "../../shared/customer-account.service";

@Component({
  selector: 'app-payment',
  templateUrl: './payment.component.html',
  imports: [
    ReactiveFormsModule,
    TranslocoDirective,
  ],
  styleUrls: []
})
export class PaymentComponent implements OnInit, OnDestroy {
  private cartService = inject(CartService);
  private paymentService = inject(PaymentService);
  private invoiceService = inject(InvoiceService);
  private formBuilder = inject(FormBuilder);
  private gaService = inject(GaService);
  private customerAccountService = inject(CustomerAccountService);

  selectedPaymentMethod: string = '';
  showValidationModal: boolean = false;
  private validationModalTimeout: ReturnType<typeof setTimeout> | null = null;

  @Input() address: any;

  paymentError: any
  paymentMessage: string;
  paymentConfirmed: boolean = false;
  cusPayment: FormGroup | any;
  private invoicePayload: any;

  paid: boolean = false;
  total: number;
  invoice_number: number;
  cart: any;
  isCzechiaUser: boolean = false;
  currencyCode: string = 'USD';

  ngOnInit(): void {
    this.cusPayment = this.formBuilder.group({
      payment_method: ['', [Validators.required]],
      bank_name: [''],
      account_name: [''],
      account_number: [''],
      gift_card_number: [''],
      validation_code: [''],
      credit_card_number: [''],
      expiration_date: [''],
      cvv: [''],
      card_holder_name: [''],
      monthly_installments: ['']
    });

    this.cusPayment.get('payment_method').valueChanges.subscribe((value: string) => {
      this.selectedPaymentMethod = value;
      this.updateValidation(value);
      this.showValidatingModal();
    });
    this.setCurrencyForUser();

    // Get cart data for purchase tracking
    this.cartService.getCart().subscribe(cart => {
      this.cart = cart;
      this.total = this.calculateTotal(cart.cart_items);
    });
  }

  updateValidation(paymentMethod: string) {
    const controls = this.cusPayment.controls;
    Object.keys(controls).forEach(key => {
      if (key !== 'payment_method') {
        controls[key].setValidators(null);
        controls[key].updateValueAndValidity();
      }
    });

    switch (paymentMethod) {
      case 'bank-transfer':
        controls['bank_name'].setValidators([Validators.required, Validators.pattern(/^[a-zA-Z ]+$/)]);
        controls['account_name'].setValidators([Validators.required, Validators.pattern(/^[a-zA-Z0-9 .'-]+$/)]);
        controls['account_number'].setValidators([Validators.required, Validators.pattern(/^\d+$/)]);
        break;
      case 'gift-card':
        controls['gift_card_number'].setValidators([Validators.required, Validators.pattern(/^[a-zA-Z0-9]+$/)]);
        controls['validation_code'].setValidators([Validators.required, Validators.pattern(/^[a-zA-Z0-9]+$/)]);
        break;
      case 'credit-card':
        controls['credit_card_number'].setValidators([Validators.pattern(/^\d{4}-\d{4}-\d{4}-\d{4}$/)]);
        controls['expiration_date'].setValidators([this.expirationDateValidator]);
        controls['cvv'].setValidators([Validators.pattern(/^\d{3,4}$/)]);
        controls['card_holder_name'].setValidators([Validators.pattern(/^[a-zA-Z ]+$/)]);
        break;
      case 'buy-now-pay-later':
        controls['monthly_installments'].setValidators([Validators.required]);
        break;
    }
  }

  private showValidatingModal(): void {
    if (!this.selectedPaymentMethod) {
      return;
    }

    this.showValidationModal = true;

    if (this.validationModalTimeout) {
      clearTimeout(this.validationModalTimeout);
    }

    this.validationModalTimeout = setTimeout(() => {
      this.showValidationModal = false;
      this.validationModalTimeout = null;
    }, 5000);
  }

  ngOnDestroy(): void {
    if (this.validationModalTimeout) {
      clearTimeout(this.validationModalTimeout);
      this.validationModalTimeout = null;
    }
  }

  expirationDateValidator(control: AbstractControl): ValidationErrors | null {
    const value = control.value;
    if (!value) {
      return null;
    }

    // Match MM/YYYY format
    if (!/^(0[1-9]|1[0-2])\/\d{4}$/.test(value)) {
      return {'dateFormat': 'Invalid date format. Use MM/YYYY.'};
    }

    const [month, year] = value.split('/').map(Number);
    const currentDate = new Date();
    const currentYear = currentDate.getFullYear();
    const currentMonth = currentDate.getMonth() + 1;

    // Check if the date is in the future
    if (year < currentYear || (year === currentYear && month < currentMonth)) {
      return {'datePast': 'Expiration date must be in the future.'};
    }

    return null;
  }

  get p(): { [key: string]: AbstractControl } {
    return this.cusPayment.controls;
  }

  confirmPayment(): void {
    const paymentData = this.cusPayment.value;

    if (paymentData.payment_method === 'payu-cz' && !this.isCzechiaUser) {
      this.paymentError = 'PayU CZ is only available for customers from Czechia.';
      return;
    }

    const payment = this.buildPaymentDetails(paymentData);
    this.invoicePayload = this.buildInvoicePayload(paymentData, payment);

    this.checkPayment({
      'payment_method': paymentData.payment_method,
      'payment_details': payment,
    }).subscribe(result => {
      if (result) {
        this.paymentConfirmed = true;
        this.cusPayment.disable();
      }
    });
  }

  buyNow(): void {
    if (!this.invoicePayload) {
      return;
    }

    this.invoiceService.createInvoice(this.invoicePayload).subscribe({
      next: (res) => {
        this.paid = true;
        this.invoice_number = res['invoice_number'];
        this.trackPurchase(res['invoice_number']);
        this.cartService.emptyCart();
      },
      error: () => {
        // handle error if needed
      }
    });
  }

  checkPayment(paymentPayload: any): Observable<boolean> {
    const endpoint = window.localStorage.getItem('PAYMENT_ENDPOINT') ?? environment.apiUrl + '/payment/check';
    return this.paymentService.validate(endpoint, paymentPayload).pipe(
      map((res) => {
        this.paymentError = null;
        this.paymentMessage = res.message;
        return true;
      }),
      catchError((err) => {
        this.paymentError = err.error?.error || 'Unknown error';
        this.paymentMessage = null;
        return of(false);
      })
    );
  }

  private buildPaymentDetails(paymentData: any): any {
    switch (paymentData.payment_method) {
      case 'bank-transfer':
        return {
          'bank_name': paymentData.bank_name,
          'account_name': paymentData.account_name,
          'account_number': paymentData.account_number
        };
      case 'gift-card':
        return {
          'gift_card_number': paymentData.gift_card_number,
          'validation_code': paymentData.validation_code
        };
      case 'credit-card':
        return {
          'credit_card_number': paymentData.credit_card_number,
          'expiration_date': paymentData.expiration_date,
          'cvv': paymentData.cvv,
          'card_holder_name': paymentData.card_holder_name
        };
      case 'buy-now-pay-later':
        return {
          'monthly_installments': paymentData.monthly_installments
        };
      case 'cash-on-delivery':
      case 'payu-cz':
        return {};
      default:
        return {};
    }
  }

  private buildInvoicePayload(paymentData: any, payment: any): any {
    const payload: any = {
      'billing_street': this.address.street,
      'billing_city': this.address.city,
      'billing_state': this.address.state,
      'billing_country': this.address.country,
      'billing_postal_code': this.address.postal_code,
      'payment_method': paymentData.payment_method,
      'payment_details': payment,
      'cart_id': sessionStorage.getItem('cart_id')
    };

    const guestInfo = sessionStorage.getItem('guestCheckout');
    if (guestInfo) {
      const guest = JSON.parse(guestInfo);
      payload['guest_email'] = guest.email;
      payload['guest_first_name'] = guest.first_name;
      payload['guest_last_name'] = guest.last_name;
    }

    return payload;
  }

  private calculateTotal(items: any[]): number {
    return items.reduce((sum, cartItem) => {
      const quantity = cartItem.quantity || 0;
      const price = cartItem.discount_percentage ? cartItem.discounted_price : cartItem.product?.price || 0;
      return sum + (quantity * price);
    }, 0);
  }

  private trackPurchase(transactionId: string): void {
    if (!this.cart?.cart_items?.length) return;

    const items = this.cart.cart_items.map((cartItem: any) => ({
      item_id: cartItem.product.id,
      item_name: cartItem.product.name,
      item_category: cartItem.product.category?.name || 'Unknown',
      item_brand: cartItem.product.brand?.name || 'Unknown',
      price: cartItem.discount_percentage ? cartItem.discounted_price : cartItem.product.price,
      quantity: cartItem.quantity
    }));

    this.gaService.trackPurchase(transactionId, this.total, this.currencyCode, items);
  }

  protected readonly JSON = JSON;

  private setCurrencyForUser(): void {
    if (!this.customerAccountService.isLoggedIn()) {
      this.applyCurrencyFromCountry('');
      return;
    }

    this.customerAccountService.getDetails().subscribe({
      next: (customer) => {
        const countryCode = customer?.address?.country || '';
        this.applyCurrencyFromCountry(countryCode);
      },
      error: () => {
        this.applyCurrencyFromCountry('');
      }
    });
  }

  private applyCurrencyFromCountry(countryCode: string): void {
    this.isCzechiaUser = countryCode === 'CZ';
    this.currencyCode = this.isCzechiaUser ? 'CZK' : 'USD';
  }
}
