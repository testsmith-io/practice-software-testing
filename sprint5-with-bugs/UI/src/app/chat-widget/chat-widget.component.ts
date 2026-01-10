import {Component, ElementRef, inject, OnInit, ViewChild} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {Router} from '@angular/router';
import {ProductService} from '../_services/product.service';
import {CartService} from '../_services/cart.service';
import {ContactService} from '../_services/contact.service';
import {InvoiceService} from '../_services/invoice.service';
import {PaymentService} from '../_services/payment.service';
import {CustomerAccountService} from '../shared/customer-account.service';
import {TokenStorageService} from '../_services/token-storage.service';
import {environment} from '../../environments/environment';
import {ChatMessage, ChatAction, ChatFlowStep, ChatProduct, SupportTicketData, OrderData, CheckoutData} from '../models/chat';

@Component({
  selector: 'app-chat-widget',
  templateUrl: './chat-widget.component.html',
  styleUrls: ['./chat-widget.component.css'],
  imports: [CommonModule, FormsModule]
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
  private tokenStorage = inject(TokenStorageService);
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
  customer: any = null;

  // Subject options for support ticket
  readonly subjectOptions = [
    {value: 'customer-service', label: 'Customer service'},
    {value: 'webmaster', label: 'Webmaster'},
    {value: 'return', label: 'Return'},
    {value: 'payments', label: 'Payments'},
    {value: 'warranty', label: 'Warranty'},
    {value: 'status-of-order', label: 'Status of my order'}
  ];

  // Common quantity options
  readonly quantityOptions = [1, 2, 3, 5, 10];

  // Checkout data (simplified - login required, simple payment)
  checkoutData: CheckoutData = {
    email: '',
    address: {
      street: '',
      city: '',
      state: '',
      country: '',
      postcode: ''
    },
    paymentMethod: '',
    accountName: '',
    accountNumber: ''
  };

  // Payment method options (same as original checkout)
  readonly paymentMethods = [
    {value: 'Bank Transfer', label: 'Bank Transfer'},
    {value: 'Cash on Delivery', label: 'Cash on Delivery'},
    {value: 'Credit Card', label: 'Credit Card'},
    {value: 'Buy Now Pay Later', label: 'Buy Now Pay Later'},
    {value: 'Gift Card', label: 'Gift Card'}
  ];

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
        this.customer = res;
        this.userName = `${res.first_name} ${res.last_name}`;
        this.supportTicket.firstName = res.first_name;
        this.supportTicket.lastName = res.last_name;
        this.supportTicket.email = res.email;
      },
      error: () => {
        this.isUserSignedIn = false;
        this.customer = null;
      }
    });
  }

  private initializeChat(): void {
    this.messages = [];
    this.addBotMessage('Hi! How can I help you today?', this.getMainMenuActions());
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
      email: '',
      address: {
        street: '',
        city: '',
        state: '',
        country: '',
        postcode: ''
      },
      paymentMethod: '',
      accountName: '',
      accountNumber: ''
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
      {label: 'Find a product', action: 'find-product'},
      {label: 'Order a product', action: 'order-product'},
      {label: 'Checkout', action: 'start-checkout'},
      {label: 'Create support ticket', action: 'support-ticket'}
    ];
  }

  private getBackAction(): ChatAction[] {
    return [{label: 'Back to menu', action: 'back-to-menu'}];
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
    actions.push({label: 'Other', action: 'custom-quantity'});
    actions.push({label: 'Back to menu', action: 'back-to-menu'});
    return actions;
  }

  private getPaymentMethodActions(): ChatAction[] {
    const actions: ChatAction[] = this.paymentMethods.map(pm => ({
      label: pm.label,
      action: 'select-payment-method',
      data: pm.value
    }));
    actions.push({label: 'Back to menu', action: 'back-to-menu'});
    return actions;
  }

  onActionClick(action: ChatAction): void {
    switch (action.action) {
      case 'find-product':
        this.currentStep = 'find-product';
        this.addBotMessage('What product are you looking for?', this.getBackAction());
        break;

      case 'order-product':
        this.resetOrderData();
        this.currentStep = 'order-product';
        this.addBotMessage('What product would you like to order? Type a product name to search.', this.getBackAction());
        break;

      case 'support-ticket':
        this.resetSupportTicket();
        this.startSupportTicketFlow();
        break;

      case 'back-to-menu':
        this.currentStep = 'main-menu';
        this.resetOrderData();
        this.resetSupportTicket();
        this.addBotMessage('Hi! How can I help you today?', this.getMainMenuActions());
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
          this.addBotMessage('Great choice! You selected:');
          this.addBotMessage(`${action.data.name} - $${action.data.price}`);
          this.addBotMessage('How many would you like to order?', this.getQuantityActions());
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
        this.addBotMessage('Please enter the quantity you want:', this.getBackAction());
        break;

      case 'confirm-order':
        this.addToCart();
        break;

      case 'change-quantity':
        this.currentStep = 'order-product-quantity';
        this.addBotMessage('How many would you like to order?', this.getQuantityActions());
        break;

      case 'continue-shopping':
        this.resetOrderData();
        this.currentStep = 'order-product';
        this.addBotMessage('What product would you like to order? Type a product name to search.', this.getBackAction());
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
          this.addBotMessage('Please describe your issue (minimum 50 characters):', this.getBackAction());
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
        this.addBotMessage('Please enter your email address:', this.getBackAction());
        break;

      case 'checkout-address-confirm':
        this.showAddressConfirmation();
        break;

      case 'checkout-edit-address':
        this.currentStep = 'checkout-address-street';
        this.addBotMessage('Please enter your street address:', this.getBackAction());
        break;

      case 'checkout-confirm-address':
        this.currentStep = 'checkout-payment-method';
        this.addBotMessage('Please select your payment method:', this.getPaymentMethodActions());
        break;

      case 'select-payment-method':
        if (action.data) {
          this.checkoutData.paymentMethod = action.data;
          this.currentStep = 'checkout-payment-account-name';
          this.addBotMessage('Please enter the account name:', [{label: 'Back to menu', action: 'back-to-menu'}]);
        }
        break;

      case 'checkout-confirm-order':
        this.processCheckout();
        break;

      case 'checkout-edit-payment':
        this.currentStep = 'checkout-payment-method';
        this.addBotMessage('Please select your payment method:', this.getPaymentMethodActions());
        break;

      case 'checkout-new-order':
        this.resetCheckoutData();
        this.currentStep = 'main-menu';
        this.addBotMessage('Hi! How can I help you today?', this.getMainMenuActions());
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

    this.addBotMessage('Order Summary:');
    this.addBotMessage(`${this.orderData.product!.name}`);
    this.addBotMessage('Quantity:');
    this.addBotMessage(`${this.orderData.quantity}`);
    this.addBotMessage('Total:');
    this.addBotMessage(`$${total}`);

    this.addBotMessage('Would you like to add this to your cart?', [
      {label: 'Yes, add to cart', action: 'confirm-order'},
      {label: 'Change quantity', action: 'change-quantity'},
      {label: 'Back to menu', action: 'back-to-menu'}
    ]);
  }

  private startSupportTicketFlow(): void {
    this.addBotMessage("I'll help you create a support ticket.");

    if (this.isUserSignedIn) {
      this.addBotMessage("I see you're already signed in. Let's get started!");
      this.currentStep = 'support-subject';
      this.addBotMessage('Please select a subject for your ticket:', this.getSubjectActions());
    } else {
      this.currentStep = 'support-first-name';
      this.addBotMessage('What is your first name?', this.getBackAction());
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
          this.addBotMessage('Please enter a valid number (1 or more).', this.getBackAction());
          return;
        }
        if (quantity > 999) {
          this.addBotMessage('Maximum quantity is 999. Please enter a smaller number.', this.getBackAction());
          return;
        }
        this.orderData.quantity = quantity;
        this.showOrderConfirmation();
        break;

      case 'support-first-name':
        this.supportTicket.firstName = input;
        this.currentStep = 'support-last-name';
        this.addBotMessage('What is your last name?', this.getBackAction());
        break;

      case 'support-last-name':
        this.supportTicket.lastName = input;
        this.currentStep = 'support-email';
        this.addBotMessage('What is your email address?', this.getBackAction());
        break;

      case 'support-email':
        if (!this.isValidEmail(input)) {
          this.addBotMessage('Please enter a valid email address.', this.getBackAction());
          return;
        }
        this.supportTicket.email = input;
        this.currentStep = 'support-subject';
        this.addBotMessage('Please select a subject for your ticket:', this.getSubjectActions());
        break;

      case 'support-message':
        if (input.length < 50) {
          this.addBotMessage('Your message must be at least 50 characters long.', this.getBackAction());
          return;
        }
        this.supportTicket.message = input;
        this.currentStep = 'support-attachment';
        this.addBotMessage('Would you like to add an attachment?', [
          {label: 'Add attachment', action: 'add-attachment'},
          {label: 'Skip', action: 'skip-attachment'}
        ]);
        break;

      // Checkout flow input handling
      case 'checkout-login':
        this.handleLoginEmail(input);
        break;

      case 'checkout-login-password':
        this.handleLoginPassword(input);
        break;

      case 'checkout-address-street':
        this.checkoutData.address.street = input;
        this.currentStep = 'checkout-address-city';
        this.addBotMessage('Please enter your city:', this.getBackAction());
        break;

      case 'checkout-address-city':
        this.checkoutData.address.city = input;
        this.currentStep = 'checkout-address-state';
        this.addBotMessage('Please enter your state/province:', this.getBackAction());
        break;

      case 'checkout-address-state':
        this.checkoutData.address.state = input;
        this.currentStep = 'checkout-address-country';
        this.addBotMessage('Please enter your country:', this.getBackAction());
        break;

      case 'checkout-address-country':
        this.checkoutData.address.country = input;
        this.currentStep = 'checkout-address-postcode';
        this.addBotMessage('Please enter your postal code:', this.getBackAction());
        break;

      case 'checkout-address-postcode':
        this.checkoutData.address.postcode = input;
        this.showAddressConfirmation();
        break;

      // Simple payment inputs (same for all methods)
      case 'checkout-payment-account-name':
        this.checkoutData.accountName = input;
        this.currentStep = 'checkout-payment-account-number';
        this.addBotMessage('Please enter the account number:', [{label: 'Back to menu', action: 'back-to-menu'}]);
        break;

      case 'checkout-payment-account-number':
        this.checkoutData.accountNumber = input;
        this.showCheckoutConfirmation();
        break;

      default:
        this.addBotMessage('Hi! How can I help you today?', this.getMainMenuActions());
        this.currentStep = 'main-menu';
    }
  }

  private handleLoginEmail(email: string): void {
    if (!this.isValidEmail(email)) {
      this.addBotMessage('Please enter a valid email address.', this.getBackAction());
      return;
    }
    this.checkoutData.email = email;
    this.currentStep = 'checkout-login-password';
    this.addBotMessage('Please enter your password:', this.getBackAction());
  }

  private handleLoginPassword(password: string): void {
    this.isLoading = true;
    const loginPayload = {
      email: this.checkoutData.email,
      password: password
    };
    this.customerAccountService.login(loginPayload).subscribe({
      next: (res) => {
        this.tokenStorage.saveToken(res.access_token);
        this.isLoading = false;
        this.isUserSignedIn = true;
        this.customerAccountService.authSub.next('changed');

        // Fetch customer details for checkout
        this.customerAccountService.getDetails().subscribe({
          next: (customer) => {
            this.customer = customer;
            // Pre-fill address from customer profile
            this.checkoutData.address.street = customer.address || '';
            this.checkoutData.address.city = customer.city || '';
            this.checkoutData.address.state = customer.state || '';
            this.checkoutData.address.country = customer.country || '';
            this.checkoutData.address.postcode = 'missing value'; // Same as original checkout

            this.addBotMessage("You're now signed in!");
            this.currentStep = 'checkout-address-street';
            this.addBotMessage('Please enter your street address:', this.getBackAction());
          },
          error: () => {
            this.addBotMessage("You're now signed in!");
            this.currentStep = 'checkout-address-street';
            this.addBotMessage('Please enter your street address:', this.getBackAction());
          }
        });
      },
      error: () => {
        this.isLoading = false;
        this.addBotMessage('Invalid email or password. Please try again.', [
          {label: 'Try again', action: 'checkout-login'},
          {label: 'Back to menu', action: 'back-to-menu'}
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
        this.addBotMessage('Only .txt files are allowed.');
        return;
      }

      if (file.size !== 0) {
        this.addBotMessage('File must be empty (0kb).');
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

    // BUG: firstName and lastName are swapped!
    const contactPayload = {
      name: `${this.supportTicket.lastName} ${this.supportTicket.firstName}`,
      email: this.supportTicket.email,
      subject: this.supportTicket.subject,
      message: this.supportTicket.message
    };

    this.contactService.sendMessage(this.supportTicket.attachment || null, contactPayload).subscribe({
      next: () => {
        this.isLoading = false;
        this.addBotMessage("Your support ticket has been submitted successfully! We'll get back to you soon.", [
          {label: 'Back to menu', action: 'back-to-menu'}
        ]);
        this.resetSupportTicket();
      },
      error: (err: any) => {
        this.isLoading = false;
        const errorMsg = typeof err === 'object' ? Object.values(err).join(' ') : 'Unknown error';
        this.addBotMessage('There was an error submitting your ticket:');
        this.addBotMessage(errorMsg, this.getBackAction());
      }
    });
  }

  private searchProducts(query: string, nextStep: ChatFlowStep): void {
    this.isLoading = true;
    this.productService.searchProducts(query).subscribe({
      next: (response) => {
        this.isLoading = false;
        const products = response.data.slice(0, 5).map((p: any) => ({
          id: p.id,
          name: p.name,
          price: p.price,
          // BUG: Wrong image path - images won't show!
          image: p.product_image?.file_name ? `assets/images/${p.product_image.file_name}` : undefined
        }));

        if (products.length === 0) {
          if (nextStep === 'order-product-results') {
            this.addBotMessage('No products found. Try a different search.', this.getBackAction());
          } else {
            this.addBotMessage('No products found. Try a different search.', this.getBackAction());
          }
        } else {
          this.currentStep = nextStep;
          if (nextStep === 'order-product-results') {
            this.addBotMessage('Here are some products I found. Click one to order:', this.getBackAction(), products);
          } else {
            this.addBotMessage('Here are some products I found:', this.getBackAction(), products);
          }
        }
      },
      error: () => {
        this.isLoading = false;
        this.addBotMessage('No products found. Try a different search.', this.getBackAction());
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
        this.addBotMessage('Added to your cart!');
        this.addBotMessage('What would you like to do next?', [
          {label: 'Continue shopping', action: 'continue-shopping'},
          {label: 'Go to checkout', action: 'checkout'},
          {label: 'Back to menu', action: 'back-to-menu'}
        ]);
        this.resetOrderData();
      },
      error: () => {
        this.isLoading = false;
        this.addBotMessage('Could not add to cart. Please try again.', this.getBackAction());
      }
    });
  }

  // Checkout flow methods (simplified - requires login)
  private startCheckoutFlow(): void {
    this.resetCheckoutData();

    // Get cart items
    const items = this.cartService.getItems();
    if (!items || items.length === 0) {
      this.addBotMessage('Your cart is empty. Please add some products first.', this.getBackAction());
      return;
    }

    this.cartTotal = this.calculateCartTotal(items);
    this.addBotMessage('Your cart total:');
    this.addBotMessage(`$${this.cartTotal.toFixed(2)}`);

    if (this.isUserSignedIn && this.customer) {
      // Already logged in - prefill address
      this.checkoutData.address.street = this.customer.address || '';
      this.checkoutData.address.city = this.customer.city || '';
      this.checkoutData.address.state = this.customer.state || '';
      this.checkoutData.address.country = this.customer.country || '';
      this.checkoutData.address.postcode = 'missing value';

      this.currentStep = 'checkout-address-street';
      this.addBotMessage('Please enter your street address:', this.getBackAction());
    } else {
      // Need to login first
      this.currentStep = 'checkout-start';
      this.addBotMessage('Please sign in to continue with checkout.', [
        {label: 'Sign in', action: 'checkout-login'},
        {label: 'Back to menu', action: 'back-to-menu'}
      ]);
    }
  }

  private calculateCartTotal(items: any[]): number {
    return items.reduce((sum: number, item: any) => {
      return sum + (Number(item.total) || 0);
    }, 0);
  }

  private showAddressConfirmation(): void {
    this.currentStep = 'checkout-address-confirm';
    this.addBotMessage('Please confirm your billing address:');
    this.addBotMessage(`${this.checkoutData.address.street}`);
    this.addBotMessage(`${this.checkoutData.address.city}, ${this.checkoutData.address.state}`);
    this.addBotMessage(`${this.checkoutData.address.country} ${this.checkoutData.address.postcode}`);

    this.addBotMessage('Is this address correct?', [
      {label: 'Confirm', action: 'checkout-confirm-address'},
      {label: 'Edit', action: 'checkout-edit-address'},
      {label: 'Back to menu', action: 'back-to-menu'}
    ]);
  }

  private showCheckoutConfirmation(): void {
    this.currentStep = 'checkout-confirm';
    this.addBotMessage('Order Summary:');
    this.addBotMessage('Total:');
    this.addBotMessage(`$${this.cartTotal.toFixed(2)}`);
    this.addBotMessage('Shipping to:');
    this.addBotMessage(`${this.checkoutData.address.street}, ${this.checkoutData.address.city}`);
    this.addBotMessage('Payment method:');
    this.addBotMessage(this.checkoutData.paymentMethod);

    this.addBotMessage('Ready to place your order?', [
      {label: 'Place Order', action: 'checkout-confirm-order'},
      {label: 'Change payment method', action: 'checkout-edit-payment'},
      {label: 'Back to menu', action: 'back-to-menu'}
    ]);
  }

  private processCheckout(): void {
    this.isLoading = true;
    this.currentStep = 'checkout-processing';
    this.addBotMessage('Processing your order...');

    // Validate payment first (same as original checkout)
    const paymentPayload = {
      method: this.checkoutData.paymentMethod,
      account_name: this.checkoutData.accountName,
      account_number: this.checkoutData.accountNumber
    };

    const endpoint = window.localStorage.getItem('PAYMENT_ENDPOINT') || `${environment.apiUrl}/payment/check`;

    this.paymentService.validate(endpoint, paymentPayload).subscribe({
      next: () => {
        // Get cart items for invoice
        const cartItems = this.cartService.getItems();
        const invoiceItems = cartItems.map((item: any) => ({
          product_id: item.id,
          unit_price: item.price,
          quantity: item.quantity
        }));

        // Create invoice (same payload structure as original checkout)
        const invoicePayload = {
          user_id: this.customer?.id,
          billing_address: this.checkoutData.address.street,
          billing_city: this.checkoutData.address.city,
          billing_state: this.checkoutData.address.state,
          billing_country: this.checkoutData.address.country,
          billing_postcode: this.checkoutData.address.postcode,
          total: this.cartTotal,
          payment_method: this.checkoutData.paymentMethod,
          payment_account_name: this.checkoutData.accountName,
          payment_account_number: this.checkoutData.accountNumber,
          invoice_items: invoiceItems
        };

        this.invoiceService.createInvoice(invoicePayload).subscribe({
          next: (res) => {
            this.isLoading = false;
            this.currentStep = 'checkout-complete';
            this.cartService.emptyCart();
            this.addBotMessage('Your order has been placed successfully!');
            this.addBotMessage('Order number:');
            this.addBotMessage(`${res.invoice_number}`);
            this.addBotMessage('What would you like to do now?', [
              {label: 'View my orders', action: 'checkout-view-orders'},
              {label: 'Start new order', action: 'checkout-new-order'}
            ]);
            this.resetCheckoutData();
          },
          error: (err: any) => {
            this.isLoading = false;
            const errorMsg = err?.error?.message || 'Unknown error';
            this.addBotMessage('There was an error processing your order:');
            this.addBotMessage(errorMsg, this.getBackAction());
          }
        });
      },
      error: (err: any) => {
        this.isLoading = false;
        const errorMsg = err?.error?.error || 'Payment validation failed';
        this.addBotMessage('Payment validation failed:');
        this.addBotMessage(errorMsg, [
          {label: 'Try again', action: 'checkout-edit-payment'},
          {label: 'Back to menu', action: 'back-to-menu'}
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
           this.currentStep === 'checkout-address-street' ||
           this.currentStep === 'checkout-address-city' ||
           this.currentStep === 'checkout-address-state' ||
           this.currentStep === 'checkout-address-country' ||
           this.currentStep === 'checkout-address-postcode' ||
           this.currentStep === 'checkout-payment-account-name' ||
           this.currentStep === 'checkout-payment-account-number';
  }
}
