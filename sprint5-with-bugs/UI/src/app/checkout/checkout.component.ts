import {Component, OnInit} from '@angular/core';
import {AbstractControl, FormBuilder, FormGroup, Validators} from "@angular/forms";
import {Observable, of} from "rxjs";
import {CartService} from "../_services/cart.service";
import {CustomerAccountService} from "../shared/customer-account.service";
import {TokenStorageService} from "../_services/token-storage.service";
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
  cusSubmitted = false;
  customerError: string | undefined;
  isLoginFailed = false;
  roles: string[] = [];

  canExitStep2 = true;
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
              private cartService: CartService,
              private customerAccountService: CustomerAccountService,
              private tokenStorage: TokenStorageService) {
  }

  ngOnInit(): void {
    this.items = this.cartService.getItems();
    this.total = this.getTotal();
    this.setAddress();
    this.isLoggedIn = this.customerAccountService.isLoggedIn();

    this.cusForm = this.formBuilder.group(
      {
        email: ['', [Validators.required, Validators.pattern("^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$")]],
        password: ['', [Validators.required,
          Validators.minLength(6),
          Validators.maxLength(40)]],
      }
    );

    this.cusAddress = this.formBuilder.group(
      {
        address: ['', [Validators.required]],
        city: ['', [Validators.required,
          Validators.minLength(6),
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

  private setAddress() {
    this.customer = this.customerAccountService.getDetails().subscribe(res => {
      this.customer = res;
      this.cusAddress.get('address').setValue(this.customer.address);
      this.cusAddress.get('city').setValue(this.customer.city);
      this.cusAddress.get('state').setValue(this.customer.state);
      this.cusAddress.get('country').setValue(this.customer.country);
      this.cusAddress.get('postcode').setValue("missing value");
    });
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

  onCusSubmit(): void {
    this.cusSubmitted = true;

    if (this.cusForm.invalid) {
      return;
    }

    const payload = {
      'email': this.cusForm.value.email,
      'password': this.cusForm.value.password
    };

    this.customerAccountService.login(payload).pipe().subscribe(res => {
      this.tokenStorage.saveToken(res.access_token);

      this.setAddress();
      this.isLoginFailed = false;
      this.isLoggedIn = true;
      this.customerAccountService.authSub.next('changed');
      this.roles = this.customerAccountService.getRole();
    }, err => {
      if (err.error === 'Unauthorized') {
        this.customerError = 'Invalid email or password';
        this.isLoginFailed = true;
      }
    });

  }

  finishFunction() {
    const invoiceItems: any = [];

    this.cartService.getItems().forEach((item: { id: number; price: number; quantity: number }) => {
      const invoiceItem = {'product_id': item.id, 'unit_price': item.price, 'quantity': item.quantity};
      invoiceItems.push(invoiceItem);
    });

    const payload = {
      'user_id': this.customer.id,
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
    if ((($event as any)?.target?.value >= 0)) {
      const quantity = (($event as any)?.target?.value <= 10) ? ($event as any)?.target?.value : 10;
      this.cartService.replaceQuantity(item.id, parseInt(quantity));
      this.items = this.cartService.getItems();
      this.total = this.getTotal();
    }
  }

}
