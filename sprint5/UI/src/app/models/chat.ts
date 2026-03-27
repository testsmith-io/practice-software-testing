export interface ChatMessage {
  id: string;
  type: 'bot' | 'user';
  content: string;
  timestamp: Date;
  actions?: ChatAction[];
  products?: ChatProduct[];
}

export interface ChatAction {
  label: string;
  action: string;
  data?: any;
}

export interface ChatProduct {
  id: string;
  name: string;
  price: number;
  image?: string;
}

export type ChatFlowStep =
  | 'welcome'
  | 'main-menu'
  | 'find-product'
  | 'find-product-results'
  | 'order-product'
  | 'order-product-results'
  | 'order-product-select'
  | 'order-product-quantity'
  | 'order-product-confirm'
  | 'order-product-added'
  | 'support-ticket'
  | 'support-first-name'
  | 'support-last-name'
  | 'support-email'
  | 'support-subject'
  | 'support-message'
  | 'support-attachment'
  | 'support-confirm'
  // Checkout flow steps
  | 'checkout-start'
  | 'checkout-login'
  | 'checkout-login-password'
  | 'checkout-guest-email'
  | 'checkout-guest-first-name'
  | 'checkout-guest-last-name'
  | 'checkout-address-street'
  | 'checkout-address-city'
  | 'checkout-address-state'
  | 'checkout-address-country'
  | 'checkout-address-postcode'
  | 'checkout-address-confirm'
  | 'checkout-payment-method'
  | 'checkout-payment-details'
  | 'checkout-payment-card-number'
  | 'checkout-payment-card-expiry'
  | 'checkout-payment-card-cvv'
  | 'checkout-payment-card-name'
  | 'checkout-payment-bank-name'
  | 'checkout-payment-account-name'
  | 'checkout-payment-account-number'
  | 'checkout-payment-bnpl'
  | 'checkout-payment-giftcard-number'
  | 'checkout-payment-giftcard-code'
  | 'checkout-confirm'
  | 'checkout-processing'
  | 'checkout-complete';

export interface OrderData {
  product: ChatProduct | null;
  quantity: number;
}

export interface CheckoutData {
  isGuest: boolean;
  email: string;
  firstName: string;
  lastName: string;
  address: {
    street: string;
    city: string;
    state: string;
    country: string;
    postcode: string;
  };
  paymentMethod: string;
  paymentDetails: {
    // Credit card
    cardNumber?: string;
    expiryDate?: string;
    cvv?: string;
    cardHolderName?: string;
    // Bank transfer
    bankName?: string;
    accountName?: string;
    accountNumber?: string;
    // BNPL
    monthlyInstallments?: number;
    // Gift card
    giftCardNumber?: string;
    validationCode?: string;
  };
}

export interface SupportTicketData {
  firstName: string;
  lastName: string;
  email: string;
  subject: string;
  message: string;
  attachment?: File;
}
