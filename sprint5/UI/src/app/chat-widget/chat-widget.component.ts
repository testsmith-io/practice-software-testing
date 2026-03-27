import {Component, ElementRef, inject, OnInit, ViewChild} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {Router} from '@angular/router';
import {TranslocoDirective} from '@jsverse/transloco';
import {ProductService} from '../_services/product.service';
import {CartService} from '../_services/cart.service';
import {ContactService} from '../_services/contact.service';
import {InvoiceService} from '../_services/invoice.service';
import {PaymentService} from '../_services/payment.service';
import {CustomerAccountService} from '../shared/customer-account.service';
import {environment} from '../../environments/environment';
import {ChatMessage, ChatAction, ChatFlowStep, ChatProduct, SupportTicketData, OrderData, CheckoutData} from '../models/chat';

@Component({
  selector: 'app-chat-widget',
  templateUrl: './chat-widget.component.html',
  styleUrls: ['./chat-widget.component.css'],
  imports: [CommonModule, FormsModule, TranslocoDirective]
})
export class ChatWidgetComponent implements OnInit {
  @ViewChild('messagesContainer') private messagesContainer: ElementRef;
  @ViewChild('fileInput') private fileInput: ElementRef;

  private productService = inject(ProductService);
  private cartService = inject(CartService);
  private contactService = inject(ContactService);
  private invoiceService = inject(InvoiceService);
  private paymentService = inject(PaymentService);
  private customerAccountService = inject(CustomerAccountService);
  private router = inject(Router);

  isOpen = false;
  messages: ChatMessage[] = [];
  userInput = '';
  currentStep: ChatFlowStep = 'welcome';
  isLoading = false;
  selectedProduct: ChatProduct | null = null;

  // Order data
  orderData: OrderData = {
    product: null,
    quantity: 1
  };

  // Support ticket data
  supportTicket: SupportTicketData = {
    firstName: '',
    lastName: '',
    email: '',
    subject: '',
    message: ''
  };
  isUserSignedIn = false;
  userName = '';

  // Subject options for support ticket
  readonly subjectOptions = [
    {value: 'customer-service', label: 'chat.support.subjects.customer-service'},
    {value: 'webmaster', label: 'chat.support.subjects.webmaster'},
    {value: 'return', label: 'chat.support.subjects.return'},
    {value: 'payments', label: 'chat.support.subjects.payments'},
    {value: 'warranty', label: 'chat.support.subjects.warranty'},
    {value: 'status-of-order', label: 'chat.support.subjects.status-of-order'}
  ];

  // Common quantity options
  readonly quantityOptions = [1, 2, 3, 5, 10];

  // Checkout data
  checkoutData: CheckoutData = {
    isGuest: false,
    email: '',
    firstName: '',
    lastName: '',
    address: {
      street: '',
      city: '',
      state: '',
      country: '',
      postcode: ''
    },
    paymentMethod: '',
    paymentDetails: {}
  };

  // Payment method options
  readonly paymentMethods = [
    {value: 'credit-card', label: 'chat.checkout.payment-methods.credit-card'},
    {value: 'bank-transfer', label: 'chat.checkout.payment-methods.bank-transfer'},
    {value: 'buy-now-pay-later', label: 'chat.checkout.payment-methods.bnpl'},
    {value: 'gift-card', label: 'chat.checkout.payment-methods.gift-card'},
    {value: 'cash-on-delivery', label: 'chat.checkout.payment-methods.cash-on-delivery'}
  ];

  // BNPL installment options
  readonly bnplOptions = [3, 6, 9, 12];

  // Cart total for display
  cartTotal = 0;

  ngOnInit(): void {
    this.initializeChat();
    this.checkUserSignedIn();
  }

  private checkUserSignedIn(): void {
    this.customerAccountService.getDetails().subscribe({
      next: (res) => {
        this.isUserSignedIn = true;
        this.userName = `${res.first_name} ${res.last_name}`;
        this.supportTicket.firstName = res.first_name;
        this.supportTicket.lastName = res.last_name;
        this.supportTicket.email = res.email;
      },
      error: () => {
        this.isUserSignedIn = false;
      }
    });
  }

  private initializeChat(): void {
    this.messages = [];
    this.addBotMessage('chat.welcome', this.getMainMenuActions());
    this.currentStep = 'main-menu';
  }

  private resetOrderData(): void {
    this.orderData = {
      product: null,
      quantity: 1
    };
    this.selectedProduct = null;
  }

  private resetSupportTicket(): void {
    if (this.isUserSignedIn) {
      this.supportTicket.subject = '';
      this.supportTicket.message = '';
      this.supportTicket.attachment = undefined;
    } else {
      this.supportTicket = {
        firstName: '',
        lastName: '',
        email: '',
        subject: '',
        message: ''
      };
    }
  }

  private resetCheckoutData(): void {
    this.checkoutData = {
      isGuest: false,
      email: '',
      firstName: '',
      lastName: '',
      address: {
        street: '',
        city: '',
        state: '',
        country: '',
        postcode: ''
      },
      paymentMethod: '',
      paymentDetails: {}
    };
    this.cartTotal = 0;
  }

  toggleChat(): void {
    this.isOpen = !this.isOpen;
    if (this.isOpen && this.messages.length === 0) {
      this.initializeChat();
    }
  }

  closeChat(): void {
    this.isOpen = false;
  }

  private addBotMessage(content: string, actions?: ChatAction[], products?: ChatProduct[]): void {
    this.messages.push({
      id: this.generateId(),
      type: 'bot',
      content,
      timestamp: new Date(),
      actions,
      products
    });
    this.scrollToBottom();
  }

  private addUserMessage(content: string): void {
    this.messages.push({
      id: this.generateId(),
      type: 'user',
      content,
      timestamp: new Date()
    });
    this.scrollToBottom();
  }

  private getMainMenuActions(): ChatAction[] {
    return [
      {label: 'chat.menu.find-product', action: 'find-product'},
      {label: 'chat.menu.order-product', action: 'order-product'},
      {label: 'chat.menu.checkout', action: 'start-checkout'},
      {label: 'chat.menu.support', action: 'support-ticket'}
    ];
  }

  private getBackAction(): ChatAction[] {
    return [{label: 'chat.back', action: 'back-to-menu'}];
  }

  private getSubjectActions(): ChatAction[] {
    return this.subjectOptions.map(opt => ({
      label: opt.label,
      action: 'select-subject',
      data: opt.value
    }));
  }

  private getQuantityActions(): ChatAction[] {
    const actions: ChatAction[] = this.quantityOptions.map(qty => ({
      label: qty.toString(),
      action: 'select-quantity',
      data: qty
    }));
    actions.push({label: 'chat.order-product.other-quantity', action: 'custom-quantity'});
    actions.push({label: 'chat.back', action: 'back-to-menu'});
    return actions;
  }

  private getLoginOrGuestActions(): ChatAction[] {
    return [
      {label: 'chat.checkout.login', action: 'checkout-login'},
      {label: 'chat.checkout.guest', action: 'checkout-guest'}
    ];
  }

  private getPaymentMethodActions(): ChatAction[] {
    const actions: ChatAction[] = this.paymentMethods.map(pm => ({
      label: pm.label,
      action: 'select-payment-method',
      data: pm.value
    }));
    actions.push({label: 'chat.back', action: 'back-to-menu'});
    return actions;
  }

  private getBnplActions(): ChatAction[] {
    const actions: ChatAction[] = this.bnplOptions.map(months => ({
      label: `${months} chat.checkout.months`,
      action: 'select-bnpl-months',
      data: months
    }));
    actions.push({label: 'chat.back', action: 'checkout-payment-method'});
    return actions;
  }

  onActionClick(action: ChatAction): void {
    switch (action.action) {
      case 'find-product':
        this.currentStep = 'find-product';
        this.addBotMessage('chat.find-product.prompt', this.getBackAction());
        break;

      case 'order-product':
        this.resetOrderData();
        this.currentStep = 'order-product';
        this.addBotMessage('chat.order-product.search-prompt', this.getBackAction());
        break;

      case 'support-ticket':
        this.resetSupportTicket();
        this.startSupportTicketFlow();
        break;

      case 'back-to-menu':
        this.currentStep = 'main-menu';
        this.resetOrderData();
        this.resetSupportTicket();
        this.addBotMessage('chat.welcome', this.getMainMenuActions());
        break;

      case 'view-product':
        if (action.data?.id) {
          this.router.navigate(['/product', action.data.id]);
          this.closeChat();
        }
        break;

      case 'select-order-product':
        if (action.data) {
          this.orderData.product = action.data;
          this.selectedProduct = action.data;
          this.currentStep = 'order-product-quantity';
          this.addBotMessage(`chat.order-product.selected`);
          this.addBotMessage(`${action.data.name} - $${action.data.price}`);
          this.addBotMessage('chat.order-product.quantity-prompt', this.getQuantityActions());
        }
        break;

      case 'select-quantity':
        if (action.data) {
          this.orderData.quantity = action.data;
          this.addUserMessage(action.data.toString());
          this.showOrderConfirmation();
        }
        break;

      case 'custom-quantity':
        this.currentStep = 'order-product-quantity';
        this.addBotMessage('chat.order-product.enter-quantity', this.getBackAction());
        break;

      case 'confirm-order':
        this.addToCart();
        break;

      case 'change-quantity':
        this.currentStep = 'order-product-quantity';
        this.addBotMessage('chat.order-product.quantity-prompt', this.getQuantityActions());
        break;

      case 'continue-shopping':
        this.resetOrderData();
        this.currentStep = 'order-product';
        this.addBotMessage('chat.order-product.search-prompt', this.getBackAction());
        break;

      case 'view-cart':
        this.router.navigate(['/checkout']);
        this.closeChat();
        break;

      case 'checkout':
        this.startCheckoutFlow();
        break;

      case 'select-subject':
        if (action.data) {
          this.supportTicket.subject = action.data;
          this.addUserMessage(action.data);
          this.currentStep = 'support-message';
          this.addBotMessage('chat.support.message-prompt', this.getBackAction());
        }
        break;

      case 'skip-attachment':
        this.submitSupportTicket();
        break;

      case 'add-attachment':
        this.triggerFileInput();
        break;

      case 'submit-ticket':
        this.submitSupportTicket();
        break;

      // Checkout flow actions
      case 'start-checkout':
        this.startCheckoutFlow();
        break;

      case 'checkout-login':
        this.currentStep = 'checkout-login';
        this.addBotMessage('chat.checkout.login-email-prompt', this.getBackAction());
        break;

      case 'checkout-guest':
        this.checkoutData.isGuest = true;
        this.currentStep = 'checkout-guest-email';
        this.addBotMessage('chat.checkout.guest-email-prompt', this.getBackAction());
        break;

      case 'checkout-address-confirm':
        this.showAddressConfirmation();
        break;

      case 'checkout-edit-address':
        this.currentStep = 'checkout-address-street';
        this.addBotMessage('chat.checkout.address-street-prompt', this.getBackAction());
        break;

      case 'checkout-confirm-address':
        this.currentStep = 'checkout-payment-method';
        this.addBotMessage('chat.checkout.payment-method-prompt', this.getPaymentMethodActions());
        break;

      case 'select-payment-method':
        if (action.data) {
          this.checkoutData.paymentMethod = action.data;
          this.checkoutData.paymentDetails = {};
          this.handlePaymentMethodSelected(action.data);
        }
        break;

      case 'select-bnpl-months':
        if (action.data) {
          this.checkoutData.paymentDetails.monthlyInstallments = action.data;
          this.showCheckoutConfirmation();
        }
        break;

      case 'checkout-confirm-order':
        this.processCheckout();
        break;

      case 'checkout-edit-payment':
        this.currentStep = 'checkout-payment-method';
        this.addBotMessage('chat.checkout.payment-method-prompt', this.getPaymentMethodActions());
        break;

      case 'checkout-back-to-payment':
        this.currentStep = 'checkout-payment-method';
        this.addBotMessage('chat.checkout.payment-method-prompt', this.getPaymentMethodActions());
        break;

      case 'checkout-new-order':
        this.resetCheckoutData();
        this.currentStep = 'main-menu';
        this.addBotMessage('chat.welcome', this.getMainMenuActions());
        break;

      case 'checkout-view-orders':
        this.router.navigate(['/account/invoices']);
        this.closeChat();
        break;
    }
  }

  private showOrderConfirmation(): void {
    this.currentStep = 'order-product-confirm';
    const total = (this.orderData.product!.price * this.orderData.quantity).toFixed(2);

    this.addBotMessage('chat.order-product.confirm-title');
    this.addBotMessage(`${this.orderData.product!.name}`);
    this.addBotMessage(`chat.order-product.confirm-quantity`);
    this.addBotMessage(`${this.orderData.quantity}`);
    this.addBotMessage(`chat.order-product.confirm-total`);
    this.addBotMessage(`$${total}`);

    this.addBotMessage('chat.order-product.confirm-prompt', [
      {label: 'chat.order-product.confirm-yes', action: 'confirm-order'},
      {label: 'chat.order-product.change-quantity', action: 'change-quantity'},
      {label: 'chat.back', action: 'back-to-menu'}
    ]);
  }

  private startSupportTicketFlow(): void {
    this.addBotMessage('chat.support.prompt');

    if (this.isUserSignedIn) {
      this.addBotMessage('chat.support.greeting-signed-in');
      this.currentStep = 'support-subject';
      this.addBotMessage('chat.support.subject-prompt', this.getSubjectActions());
    } else {
      this.currentStep = 'support-first-name';
      this.addBotMessage('chat.support.first-name-prompt', this.getBackAction());
    }
  }

  onProductClick(product: ChatProduct): void {
    if (this.currentStep === 'find-product-results') {
      this.router.navigate(['/product', product.id]);
      this.closeChat();
    } else if (this.currentStep === 'order-product-results') {
      this.onActionClick({
        label: '',
        action: 'select-order-product',
        data: product
      });
    }
  }

  onSubmit(): void {
    const input = this.userInput.trim();
    if (!input) return;

    this.addUserMessage(input);
    this.userInput = '';

    switch (this.currentStep) {
      case 'find-product':
        this.searchProducts(input, 'find-product-results');
        break;

      case 'order-product':
        this.searchProducts(input, 'order-product-results');
        break;

      case 'order-product-quantity':
        const quantity = parseInt(input, 10);
        if (isNaN(quantity) || quantity < 1) {
          this.addBotMessage('chat.order-product.invalid-quantity', this.getBackAction());
          return;
        }
        if (quantity > 999) {
          this.addBotMessage('chat.order-product.quantity-too-high', this.getBackAction());
          return;
        }
        this.orderData.quantity = quantity;
        this.showOrderConfirmation();
        break;

      case 'support-first-name':
        this.supportTicket.firstName = input;
        this.currentStep = 'support-last-name';
        this.addBotMessage('chat.support.last-name-prompt', this.getBackAction());
        break;

      case 'support-last-name':
        this.supportTicket.lastName = input;
        this.currentStep = 'support-email';
        this.addBotMessage('chat.support.email-prompt', this.getBackAction());
        break;

      case 'support-email':
        if (!this.isValidEmail(input)) {
          this.addBotMessage('chat.support.email-invalid', this.getBackAction());
          return;
        }
        this.supportTicket.email = input;
        this.currentStep = 'support-subject';
        this.addBotMessage('chat.support.subject-prompt', this.getSubjectActions());
        break;

      case 'support-message':
        if (input.length < 50) {
          this.addBotMessage('chat.support.message-too-short', this.getBackAction());
          return;
        }
        this.supportTicket.message = input;
        this.currentStep = 'support-attachment';
        this.addBotMessage('chat.support.attachment-prompt', [
          {label: 'chat.support.add-attachment', action: 'add-attachment'},
          {label: 'chat.support.skip-attachment', action: 'skip-attachment'}
        ]);
        break;

      // Checkout flow input handling
      case 'checkout-login':
        this.handleLoginEmail(input);
        break;

      case 'checkout-login-password':
        this.handleLoginPassword(input);
        break;

      case 'checkout-guest-email':
        if (!this.isValidEmail(input)) {
          this.addBotMessage('chat.checkout.invalid-email', this.getBackAction());
          return;
        }
        this.checkoutData.email = input;
        this.currentStep = 'checkout-guest-first-name';
        this.addBotMessage('chat.checkout.guest-first-name-prompt', this.getBackAction());
        break;

      case 'checkout-guest-first-name':
        this.checkoutData.firstName = input;
        this.currentStep = 'checkout-guest-last-name';
        this.addBotMessage('chat.checkout.guest-last-name-prompt', this.getBackAction());
        break;

      case 'checkout-guest-last-name':
        this.checkoutData.lastName = input;
        this.currentStep = 'checkout-address-street';
        this.addBotMessage('chat.checkout.address-street-prompt', this.getBackAction());
        break;

      case 'checkout-address-street':
        this.checkoutData.address.street = input;
        this.currentStep = 'checkout-address-city';
        this.addBotMessage('chat.checkout.address-city-prompt', this.getBackAction());
        break;

      case 'checkout-address-city':
        this.checkoutData.address.city = input;
        this.currentStep = 'checkout-address-state';
        this.addBotMessage('chat.checkout.address-state-prompt', this.getBackAction());
        break;

      case 'checkout-address-state':
        this.checkoutData.address.state = input;
        this.currentStep = 'checkout-address-country';
        this.addBotMessage('chat.checkout.address-country-prompt', this.getBackAction());
        break;

      case 'checkout-address-country':
        this.checkoutData.address.country = input;
        this.currentStep = 'checkout-address-postcode';
        this.addBotMessage('chat.checkout.address-postcode-prompt', this.getBackAction());
        break;

      case 'checkout-address-postcode':
        this.checkoutData.address.postcode = input;
        this.showAddressConfirmation();
        break;

      // Credit card inputs
      case 'checkout-payment-card-number':
        if (!/^\d{4}-\d{4}-\d{4}-\d{4}$/.test(input)) {
          this.addBotMessage('chat.checkout.invalid-card-number', [{label: 'chat.back', action: 'checkout-back-to-payment'}]);
          return;
        }
        this.checkoutData.paymentDetails.cardNumber = input;
        this.currentStep = 'checkout-payment-card-expiry';
        this.addBotMessage('chat.checkout.card-expiry-prompt', [{label: 'chat.back', action: 'checkout-back-to-payment'}]);
        break;

      case 'checkout-payment-card-expiry':
        if (!/^(0[1-9]|1[0-2])\/\d{4}$/.test(input)) {
          this.addBotMessage('chat.checkout.invalid-expiry', [{label: 'chat.back', action: 'checkout-back-to-payment'}]);
          return;
        }
        this.checkoutData.paymentDetails.expiryDate = input;
        this.currentStep = 'checkout-payment-card-cvv';
        this.addBotMessage('chat.checkout.card-cvv-prompt', [{label: 'chat.back', action: 'checkout-back-to-payment'}]);
        break;

      case 'checkout-payment-card-cvv':
        if (!/^\d{3,4}$/.test(input)) {
          this.addBotMessage('chat.checkout.invalid-cvv', [{label: 'chat.back', action: 'checkout-back-to-payment'}]);
          return;
        }
        this.checkoutData.paymentDetails.cvv = input;
        this.currentStep = 'checkout-payment-card-name';
        this.addBotMessage('chat.checkout.card-name-prompt', [{label: 'chat.back', action: 'checkout-back-to-payment'}]);
        break;

      case 'checkout-payment-card-name':
        if (!/^[a-zA-Z ]+$/.test(input)) {
          this.addBotMessage('chat.checkout.invalid-card-name', [{label: 'chat.back', action: 'checkout-back-to-payment'}]);
          return;
        }
        this.checkoutData.paymentDetails.cardHolderName = input;
        this.showCheckoutConfirmation();
        break;

      // Bank transfer inputs
      case 'checkout-payment-bank-name':
        if (!/^[a-zA-Z ]+$/.test(input)) {
          this.addBotMessage('chat.checkout.invalid-bank-name', [{label: 'chat.back', action: 'checkout-back-to-payment'}]);
          return;
        }
        this.checkoutData.paymentDetails.bankName = input;
        this.currentStep = 'checkout-payment-account-name';
        this.addBotMessage('chat.checkout.account-name-prompt', [{label: 'chat.back', action: 'checkout-back-to-payment'}]);
        break;

      case 'checkout-payment-account-name':
        if (!/^[a-zA-Z0-9 .'-]+$/.test(input)) {
          this.addBotMessage('chat.checkout.invalid-account-name', [{label: 'chat.back', action: 'checkout-back-to-payment'}]);
          return;
        }
        this.checkoutData.paymentDetails.accountName = input;
        this.currentStep = 'checkout-payment-account-number';
        this.addBotMessage('chat.checkout.account-number-prompt', [{label: 'chat.back', action: 'checkout-back-to-payment'}]);
        break;

      case 'checkout-payment-account-number':
        if (!/^\d+$/.test(input)) {
          this.addBotMessage('chat.checkout.invalid-account-number', [{label: 'chat.back', action: 'checkout-back-to-payment'}]);
          return;
        }
        this.checkoutData.paymentDetails.accountNumber = input;
        this.showCheckoutConfirmation();
        break;

      // Gift card inputs
      case 'checkout-payment-giftcard-number':
        if (!/^[a-zA-Z0-9]+$/.test(input)) {
          this.addBotMessage('chat.checkout.invalid-giftcard-number', [{label: 'chat.back', action: 'checkout-back-to-payment'}]);
          return;
        }
        this.checkoutData.paymentDetails.giftCardNumber = input;
        this.currentStep = 'checkout-payment-giftcard-code';
        this.addBotMessage('chat.checkout.giftcard-code-prompt', [{label: 'chat.back', action: 'checkout-back-to-payment'}]);
        break;

      case 'checkout-payment-giftcard-code':
        if (!/^[a-zA-Z0-9]+$/.test(input)) {
          this.addBotMessage('chat.checkout.invalid-giftcard-code', [{label: 'chat.back', action: 'checkout-back-to-payment'}]);
          return;
        }
        this.checkoutData.paymentDetails.validationCode = input;
        this.showCheckoutConfirmation();
        break;

      default:
        this.addBotMessage('chat.welcome', this.getMainMenuActions());
        this.currentStep = 'main-menu';
    }
  }

  private handleLoginEmail(email: string): void {
    if (!this.isValidEmail(email)) {
      this.addBotMessage('chat.checkout.invalid-email', this.getBackAction());
      return;
    }
    this.checkoutData.email = email;
    this.currentStep = 'checkout-login-password';
    this.addBotMessage('chat.checkout.login-password-prompt', this.getBackAction());
  }

  private handleLoginPassword(password: string): void {
    this.isLoading = true;
    const loginPayload = {
      email: this.checkoutData.email,
      password: password
    };
    this.customerAccountService.login(loginPayload).subscribe({
      next: () => {
        this.isLoading = false;
        this.isUserSignedIn = true;
        this.checkoutData.isGuest = false;
        this.addBotMessage('chat.checkout.login-success');
        this.currentStep = 'checkout-address-street';
        this.addBotMessage('chat.checkout.address-street-prompt', this.getBackAction());
      },
      error: () => {
        this.isLoading = false;
        this.addBotMessage('chat.checkout.login-error', [
          {label: 'chat.checkout.try-again', action: 'checkout-login'},
          {label: 'chat.checkout.guest', action: 'checkout-guest'},
          {label: 'chat.back', action: 'back-to-menu'}
        ]);
      }
    });
  }

  private isValidEmail(email: string): boolean {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  triggerFileInput(): void {
    if (this.fileInput) {
      this.fileInput.nativeElement.click();
    }
  }

  onFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
      const file = input.files[0];

      if (file.type !== 'text/plain') {
        this.addBotMessage('chat.support.attachment-invalid-type');
        return;
      }

      if (file.size !== 0) {
        this.addBotMessage('chat.support.attachment-invalid-size');
        return;
      }

      this.supportTicket.attachment = file;
      this.addUserMessage(file.name);
      this.submitSupportTicket();
    }
  }

  private submitSupportTicket(): void {
    this.isLoading = true;
    this.currentStep = 'support-confirm';

    const contactPayload = {
      name: `${this.supportTicket.firstName} ${this.supportTicket.lastName}`,
      email: this.supportTicket.email,
      subject: this.supportTicket.subject,
      message: this.supportTicket.message
    };

    this.contactService.sendMessage(this.supportTicket.attachment || null, contactPayload).subscribe({
      next: () => {
        this.isLoading = false;
        this.addBotMessage('chat.support.success', [
          {label: 'chat.back', action: 'back-to-menu'}
        ]);
        this.resetSupportTicket();
      },
      error: (err) => {
        this.isLoading = false;
        const errorMsg = typeof err === 'object' ? Object.values(err).join(' ') : 'Unknown error';
        this.addBotMessage('chat.support.error');
        this.addBotMessage(errorMsg, this.getBackAction());
      }
    });
  }

  private searchProducts(query: string, nextStep: ChatFlowStep): void {
    this.isLoading = true;
    this.productService.searchProducts(query).subscribe({
      next: (response) => {
        this.isLoading = false;
        const products = response.data.slice(0, 5).map(p => ({
          id: p.id,
          name: p.name,
          price: p.price,
          image: p.product_image?.file_name ? `assets/img/products/${p.product_image.file_name}` : undefined
        }));

        if (products.length === 0) {
          if (nextStep === 'order-product-results') {
            this.addBotMessage('chat.order-product.no-results', this.getBackAction());
          } else {
            this.addBotMessage('chat.find-product.no-results', this.getBackAction());
          }
        } else {
          this.currentStep = nextStep;
          if (nextStep === 'order-product-results') {
            this.addBotMessage('chat.order-product.select-product', this.getBackAction(), products);
          } else {
            this.addBotMessage('chat.find-product.results', this.getBackAction(), products);
          }
        }
      },
      error: () => {
        this.isLoading = false;
        if (nextStep === 'order-product-results') {
          this.addBotMessage('chat.order-product.no-results', this.getBackAction());
        } else {
          this.addBotMessage('chat.find-product.no-results', this.getBackAction());
        }
      }
    });
  }

  private addToCart(): void {
    if (!this.orderData.product) return;

    this.isLoading = true;
    this.currentStep = 'order-product-added';

    this.cartService.addItem({id: this.orderData.product.id, quantity: this.orderData.quantity}).subscribe({
      next: () => {
        this.isLoading = false;
        this.addBotMessage('chat.order-product.success');
        this.addBotMessage('chat.order-product.what-next', [
          {label: 'chat.order-product.continue-shopping', action: 'continue-shopping'},
          {label: 'chat.order-product.go-to-checkout', action: 'checkout'},
          {label: 'chat.back', action: 'back-to-menu'}
        ]);
        this.resetOrderData();
      },
      error: () => {
        this.isLoading = false;
        this.addBotMessage('chat.order-product.error', this.getBackAction());
      }
    });
  }

  // Checkout flow methods
  private startCheckoutFlow(): void {
    this.resetCheckoutData();
    this.cartService.getCart().subscribe({
      next: (cart) => {
        if (!cart.cart_items || cart.cart_items.length === 0) {
          this.addBotMessage('chat.checkout.cart-empty', this.getBackAction());
          return;
        }

        this.cartTotal = this.calculateCartTotal(cart.cart_items);
        this.addBotMessage('chat.checkout.cart-summary');
        this.addBotMessage(`$${this.cartTotal.toFixed(2)}`);

        if (this.isUserSignedIn) {
          this.checkoutData.isGuest = false;
          this.currentStep = 'checkout-address-street';
          this.addBotMessage('chat.checkout.address-street-prompt', this.getBackAction());
        } else {
          this.currentStep = 'checkout-start';
          this.addBotMessage('chat.checkout.login-or-guest', this.getLoginOrGuestActions());
        }
      },
      error: () => {
        this.addBotMessage('chat.checkout.cart-error', this.getBackAction());
      }
    });
  }

  private calculateCartTotal(items: any[]): number {
    return items.reduce((sum, cartItem) => {
      const quantity = cartItem.quantity || 0;
      const price = cartItem.discount_percentage ? cartItem.discounted_price : cartItem.product?.price || 0;
      return sum + (quantity * price);
    }, 0);
  }

  private showAddressConfirmation(): void {
    this.currentStep = 'checkout-address-confirm';
    this.addBotMessage('chat.checkout.address-confirm-title');
    this.addBotMessage(`${this.checkoutData.address.street}`);
    this.addBotMessage(`${this.checkoutData.address.city}, ${this.checkoutData.address.state}`);
    this.addBotMessage(`${this.checkoutData.address.country} ${this.checkoutData.address.postcode}`);

    this.addBotMessage('chat.checkout.address-confirm-prompt', [
      {label: 'chat.checkout.confirm', action: 'checkout-confirm-address'},
      {label: 'chat.checkout.edit', action: 'checkout-edit-address'},
      {label: 'chat.back', action: 'back-to-menu'}
    ]);
  }

  private handlePaymentMethodSelected(method: string): void {
    switch (method) {
      case 'credit-card':
        this.currentStep = 'checkout-payment-card-number';
        this.addBotMessage('chat.checkout.card-number-prompt', [{label: 'chat.back', action: 'checkout-back-to-payment'}]);
        break;

      case 'bank-transfer':
        this.currentStep = 'checkout-payment-bank-name';
        this.addBotMessage('chat.checkout.bank-name-prompt', [{label: 'chat.back', action: 'checkout-back-to-payment'}]);
        break;

      case 'buy-now-pay-later':
        this.currentStep = 'checkout-payment-bnpl';
        this.addBotMessage('chat.checkout.bnpl-prompt', this.getBnplActions());
        break;

      case 'gift-card':
        this.currentStep = 'checkout-payment-giftcard-number';
        this.addBotMessage('chat.checkout.giftcard-number-prompt', [{label: 'chat.back', action: 'checkout-back-to-payment'}]);
        break;

      case 'cash-on-delivery':
        this.showCheckoutConfirmation();
        break;
    }
  }

  private showCheckoutConfirmation(): void {
    this.currentStep = 'checkout-confirm';
    this.addBotMessage('chat.checkout.order-summary');
    this.addBotMessage('chat.checkout.total-label');
    this.addBotMessage(`$${this.cartTotal.toFixed(2)}`);
    this.addBotMessage('chat.checkout.shipping-address');
    this.addBotMessage(`${this.checkoutData.address.street}, ${this.checkoutData.address.city}`);
    this.addBotMessage('chat.checkout.payment-label');
    this.addBotMessage(this.getPaymentMethodLabel(this.checkoutData.paymentMethod));

    this.addBotMessage('chat.checkout.confirm-order-prompt', [
      {label: 'chat.checkout.place-order', action: 'checkout-confirm-order'},
      {label: 'chat.checkout.edit-payment', action: 'checkout-edit-payment'},
      {label: 'chat.back', action: 'back-to-menu'}
    ]);
  }

  private getPaymentMethodLabel(method: string): string {
    const pm = this.paymentMethods.find(p => p.value === method);
    return pm ? pm.label : method;
  }

  private processCheckout(): void {
    this.isLoading = true;
    this.currentStep = 'checkout-processing';
    this.addBotMessage('chat.checkout.processing');

    const cartId = sessionStorage.getItem('cart_id');

    let paymentDetails: any = {};
    switch (this.checkoutData.paymentMethod) {
      case 'bank-transfer':
        paymentDetails = {
          bank_name: this.checkoutData.paymentDetails.bankName,
          account_name: this.checkoutData.paymentDetails.accountName,
          account_number: this.checkoutData.paymentDetails.accountNumber
        };
        break;
      case 'gift-card':
        paymentDetails = {
          gift_card_number: this.checkoutData.paymentDetails.giftCardNumber,
          validation_code: this.checkoutData.paymentDetails.validationCode
        };
        break;
      case 'credit-card':
        paymentDetails = {
          credit_card_number: this.checkoutData.paymentDetails.cardNumber,
          expiration_date: this.checkoutData.paymentDetails.expiryDate,
          cvv: this.checkoutData.paymentDetails.cvv,
          card_holder_name: this.checkoutData.paymentDetails.cardHolderName
        };
        break;
      case 'buy-now-pay-later':
        paymentDetails = {
          monthly_installments: this.checkoutData.paymentDetails.monthlyInstallments
        };
        break;
      case 'cash-on-delivery':
        paymentDetails = {};
        break;
    }

    const paymentPayload = {
      payment_method: this.checkoutData.paymentMethod,
      payment_details: paymentDetails
    };

    const endpoint = window.localStorage.getItem('PAYMENT_ENDPOINT') || `${environment.apiUrl}/payment/check`;

    this.paymentService.validate(endpoint, paymentPayload).subscribe({
      next: () => {
        const invoicePayload: any = {
          billing_street: this.checkoutData.address.street,
          billing_city: this.checkoutData.address.city,
          billing_state: this.checkoutData.address.state,
          billing_country: this.checkoutData.address.country,
          billing_postal_code: this.checkoutData.address.postcode,
          payment_method: this.checkoutData.paymentMethod,
          payment_details: paymentDetails,
          cart_id: cartId
        };

        // Add guest info for guest checkout
        if (this.checkoutData.isGuest) {
          invoicePayload.guest_email = this.checkoutData.email;
          invoicePayload.guest_first_name = this.checkoutData.firstName;
          invoicePayload.guest_last_name = this.checkoutData.lastName;
        }

        this.invoiceService.createInvoice(invoicePayload).subscribe({
          next: (res) => {
            this.isLoading = false;
            this.currentStep = 'checkout-complete';
            this.cartService.emptyCart();
            this.addBotMessage('chat.checkout.success');
            this.addBotMessage(`chat.checkout.order-number`);
            this.addBotMessage(`${res.invoice_number}`);
            this.addBotMessage('chat.checkout.what-next', [
              {label: 'chat.checkout.view-orders', action: 'checkout-view-orders'},
              {label: 'chat.checkout.new-order', action: 'checkout-new-order'}
            ]);
            this.resetCheckoutData();
          },
          error: (err) => {
            this.isLoading = false;
            const errorMsg = err?.error?.message || 'Unknown error';
            this.addBotMessage('chat.checkout.error');
            this.addBotMessage(errorMsg, this.getBackAction());
          }
        });
      },
      error: (err) => {
        this.isLoading = false;
        const errorMsg = err?.error?.error || 'Payment validation failed';
        this.addBotMessage('chat.checkout.payment-error');
        this.addBotMessage(errorMsg, [
          {label: 'chat.checkout.try-again', action: 'checkout-edit-payment'},
          {label: 'chat.back', action: 'back-to-menu'}
        ]);
      }
    });
  }

  private generateId(): string {
    return Math.random().toString(36).substring(2, 9);
  }

  private scrollToBottom(): void {
    setTimeout(() => {
      if (this.messagesContainer) {
        this.messagesContainer.nativeElement.scrollTop =
          this.messagesContainer.nativeElement.scrollHeight;
      }
    }, 100);
  }

  showInput(): boolean {
    return this.currentStep === 'find-product' ||
           this.currentStep === 'order-product' ||
           this.currentStep === 'order-product-quantity' ||
           this.currentStep === 'support-first-name' ||
           this.currentStep === 'support-last-name' ||
           this.currentStep === 'support-email' ||
           this.currentStep === 'support-message' ||
           // Checkout flow input steps
           this.currentStep === 'checkout-login' ||
           this.currentStep === 'checkout-login-password' ||
           this.currentStep === 'checkout-guest-email' ||
           this.currentStep === 'checkout-guest-first-name' ||
           this.currentStep === 'checkout-guest-last-name' ||
           this.currentStep === 'checkout-address-street' ||
           this.currentStep === 'checkout-address-city' ||
           this.currentStep === 'checkout-address-state' ||
           this.currentStep === 'checkout-address-country' ||
           this.currentStep === 'checkout-address-postcode' ||
           this.currentStep === 'checkout-payment-card-number' ||
           this.currentStep === 'checkout-payment-card-expiry' ||
           this.currentStep === 'checkout-payment-card-cvv' ||
           this.currentStep === 'checkout-payment-card-name' ||
           this.currentStep === 'checkout-payment-bank-name' ||
           this.currentStep === 'checkout-payment-account-name' ||
           this.currentStep === 'checkout-payment-account-number' ||
           this.currentStep === 'checkout-payment-giftcard-number' ||
           this.currentStep === 'checkout-payment-giftcard-code';
  }
}
