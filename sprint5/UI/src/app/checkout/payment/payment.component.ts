import {Component, Input, OnInit} from '@angular/core';
import {AbstractControl, FormBuilder, FormGroup, ValidationErrors, Validators} from "@angular/forms";
import {CartService} from "../../_services/cart.service";
import {Observable, of} from "rxjs";
import {environment} from "../../../environments/environment";
import {PaymentService} from "../../_services/payment.service";
import {InvoiceService} from "../../_services/invoice.service";

@Component({
  selector: 'app-payment',
  templateUrl: './payment.component.html',
  styleUrl: './payment.component.css'
})
export class PaymentComponent implements OnInit {

  selectedPaymentMethod: string = '';

  @Input() address: any;

  paymentError: any
  state: any;
  paymentMessage: string;
  cusPayment: FormGroup | any;

  paid: boolean = false;
  total: number;
  invoice_number: number;

  constructor(private cartService: CartService,
              private paymentService: PaymentService,
              private invoiceService: InvoiceService,
              private formBuilder: FormBuilder) {
  }

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

  finishFunction() {
    let cartId = sessionStorage.getItem('cart_id');
    let paymentData = this.cusPayment.value;

    let payment: any;
    switch (paymentData.payment_method) {
      case 'bank-transfer':
        payment = {
          'bank_name': paymentData.bank_name,
          'account_name': paymentData.account_name,
          'account_number': paymentData.account_number
        }
        break;
      case 'gift-card':
        payment = {
          'gift_card_number': paymentData.gift_card_number,
          'validation_code': paymentData.validation_code
        }
        break;
      case 'credit-card':
        payment = {
          'credit_card_number': paymentData.credit_card_number,
          'expiration_date': paymentData.expiration_date,
          'cvv': paymentData.cvv,
          'card_holder_name': paymentData.card_holder_name
        }
        break;
      case 'buy-now-pay-later':
        payment = {
          'monthly_installments': paymentData.monthly_installments
        }
        break;
    }

    const payload = {
      'billing_address': this.address.value.address,
      'billing_city': this.address.value.city,
      'billing_state': this.address.value.state,
      'billing_country': this.address.value.country,
      'billing_postcode': this.address.value.postcode,
      'payment_method': paymentData.payment_method,
      'payment_details': payment,
      'cart_id': cartId
    };

    const payloadPayload = {
      'payment_method': paymentData.payment_method,
      'payment_details': payment,
    };

    this.checkPayment(payloadPayload).subscribe(result => {
      if (result === true) {
        this.invoiceService.createInvoice(payload).subscribe(res => {
          this.paid = true;
          this.invoice_number = res['invoice_number'];
          this.cartService.emptyCart();
        }, () => {
        });
      }
    })
  }

  /*
  Check payment method, only if mock endpoint is stored in sessionStorage
   */
  checkPayment(paymentPayload: any): Observable<boolean> {
    if (!this.state) {
      const endpoint = (window.localStorage.getItem('PAYMENT_ENDPOINT')) ? window.localStorage.getItem('PAYMENT_ENDPOINT') : environment.apiUrl + '/payment/check';
      this.paymentService.validate(endpoint, paymentPayload).subscribe(res => {
        this.paymentError = null;
        this.paymentMessage = res.message;
        this.state = true;
      }, err => {
        this.state = null;
        this.paymentError = err.error.error;
        this.state = false;
      });
    }
    return of(this.state);
  }

  protected readonly JSON = JSON;
}
