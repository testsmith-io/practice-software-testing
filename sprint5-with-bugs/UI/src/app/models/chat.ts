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
  // Checkout flow steps (simplified - login required, no guest)
  | 'checkout-start'
  | 'checkout-login'
  | 'checkout-login-password'
  | 'checkout-address-street'
  | 'checkout-address-city'
  | 'checkout-address-state'
  | 'checkout-address-country'
  | 'checkout-address-postcode'
  | 'checkout-address-confirm'
  | 'checkout-payment-method'
  | 'checkout-payment-account-name'
  | 'checkout-payment-account-number'
  | 'checkout-confirm'
  | 'checkout-processing'
  | 'checkout-complete';

export interface OrderData {
  product: ChatProduct | null;
  quantity: number;
}

export interface CheckoutData {
  email: string;
  address: {
    street: string;
    city: string;
    state: string;
    country: string;
    postcode: string;
  };
  paymentMethod: string;
  accountName: string;
  accountNumber: string;
}

export interface SupportTicketData {
  firstName: string;
  lastName: string;
  email: string;
  subject: string;
  message: string;
  attachment?: File;
}
