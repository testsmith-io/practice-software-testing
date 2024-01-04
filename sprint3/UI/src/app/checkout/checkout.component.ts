import {Component, OnInit} from '@angular/core';
import {AbstractControl, FormBuilder, FormGroup, Validators} from "@angular/forms";
import {Observable, of} from "rxjs";
import {CartService} from "../_services/cart.service";
import {InvoiceService} from "../_services/invoice.service";
import {PaymentService} from "../_services/payment.service";
import {environment} from "../../environments/environment";

@Component({
  selector: 'app-checkout',
  templateUrl: './checkout.component.html',
  styleUrls: ['./checkout.component.css']
})
export class CheckoutComponent implements OnInit {

  PaymentMethods: any = ['Bank Transfer', 'Cash on Delivery', 'Credit Card', 'Buy Now Pay Later', 'Gift Card'];
  cusAddress: FormGroup | any;
  cusPayment: FormGroup | any;
  cusForm: FormGroup | any;
  roles: string[] = [];

  items: any;
  isLoggedIn: boolean = false;
  customer: any;

  paymentError: any
  state: any;
  paymentMessage: string;

  paid: boolean = false;
  total: number;
  invoice_number: number;

  constructor(private paymentService: PaymentService,
              private invoiceService: InvoiceService,
              private formBuilder: FormBuilder,
              private cartService: CartService) {
  }

  ngOnInit(): void {
    this.items = this.cartService.getItems();
    this.total = this.getTotal();

    this.cusAddress = this.formBuilder.group(
      {
        first_name: ['', [Validators.required]],
        last_name: ['', [Validators.required]],
        email: ['', [Validators.required, Validators.pattern("^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$")]],
        address: ['', [Validators.required]],
        city: ['', [Validators.required,
          Validators.minLength(3),
          Validators.maxLength(40)]],
        state: ['', [Validators.required]],
        country: ['', [Validators.required]],
        postcode: ['', [Validators.required]],
      }
    );

    this.cusPayment = this.formBuilder.group(
      {
        payment_method: ['', [Validators.required]],
        account_name: ['', [Validators.required]],
        account_number: ['', [Validators.required]],
      }
    );
  }

  get f(): { [key: string]: AbstractControl } {
    return this.cusAddress.controls;
  }

  get p(): { [key: string]: AbstractControl } {
    return this.cusPayment.controls;
  }

  get cus_email() {
    return this.cusForm.get('email');
  }

  get cus_password() {
    return this.cusForm.get('password');
  }

  get cf(): { [key: string]: AbstractControl } {
    return this.cusForm.controls;
  }

  delete(id: number) {
    this.cartService.deleteItem(id);
    this.items = this.cartService.getItems();
    this.total = this.getTotal();
  }

  private getTotal() {
    const items = this.cartService.getItems();
    if (items != null && items.length) {
      return Math.floor(items
        .reduce((sum: number, current: { total: any; }) => sum + Number(current.total), 0) * 100) / 100;
    } else {
      return 0;
    }
  }

  finishFunction() {
    const invoiceItems: any = [];

    this.cartService.getItems().forEach((item: { id: number; price: number; quantity: number }) => {
      const invoiceItem = {'product_id': item.id, 'unit_price': item.price, 'quantity': item.quantity};
      invoiceItems.push(invoiceItem);
    });

    const payload = {
      'first_name': this.cusAddress.value.first_name,
      'last_name': this.cusAddress.value.last_name,
      'email': this.cusAddress.value.email,
      'billing_address': this.cusAddress.value.address,
      'billing_city': this.cusAddress.value.city,
      'billing_state': this.cusAddress.value.state,
      'billing_country': this.cusAddress.value.country,
      'billing_postcode': this.cusAddress.value.postcode,
      'total': this.getTotal(),
      'payment_method': this.cusPayment.value.payment_method,
      'payment_account_name': this.cusPayment.value.account_name,
      'payment_account_number': this.cusPayment.value.account_number,
      'invoice_items': invoiceItems
    };

    this.checkPayment().subscribe(result => {
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
  checkPayment(): Observable<boolean> {
    if (!this.state) {
      const payload = {
        'method': this.cusPayment.value.payment_method,
        'account_name': this.cusPayment.value.account_name,
        'account_number': this.cusPayment.value.account_number
      }
      const endpoint = (window.localStorage.getItem('PAYMENT_ENDPOINT')) ? window.localStorage.getItem('PAYMENT_ENDPOINT') : environment.apiUrl + '/payment/check';
      this.paymentService.validate(endpoint, payload).subscribe(res => {
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
  updateQuantity($event: Event, item: any) {
    const quantity = (($event as any)?.target?.value >= 1) ? ($event as any)?.target?.value : 1;
    this.cartService.replaceQuantity(item.id, parseInt(quantity));
    this.items = this.cartService.getItems();
    this.total = this.getTotal();
  }

}
