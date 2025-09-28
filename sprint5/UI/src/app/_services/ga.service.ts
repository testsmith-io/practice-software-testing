import {Injectable, inject} from '@angular/core';
import {Router, NavigationEnd} from '@angular/router';
import {filter} from 'rxjs/operators';
import {environment} from '../../environments/environment';

declare global {
  interface Window {
    gtag: (...args: any[]) => void;
    dataLayer: any[];
  }
}

@Injectable({
  providedIn: 'root'
})
export class GaService {
  private router = inject(Router);
  private isInitialized = false;
  private isReady = false;
  private eventQueue: Array<() => void> = [];

  constructor() {
    if (environment.production && environment.gaCode && !this.isInitialized) {
      console.log('Initializing Google Analytics 4:', environment.gaCode);
      this.initializeGA4(environment.gaCode);
      this.setupNavigationTracking();
      this.isInitialized = true;
    } else if (!environment.production) {
      console.log('Google Analytics disabled in development mode');
    }
  }

  private initializeGA4(gaCode: string): void {
    // Check if GA script already exists
    if (document.querySelector(`script[src*="googletagmanager.com/gtag/js?id=${gaCode}"]`)) {
      console.warn('Google Analytics script already loaded');
      return;
    }

    // Initialize dataLayer only once
    if (!window.dataLayer) {
      window.dataLayer = [];
    }

    // Define gtag function only once
    if (!window.gtag) {
      window.gtag = function() {
        window.dataLayer.push(arguments);
      };
    }

    // Load GA4 script
    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${gaCode}`;
    script.onload = () => {
      // Configure GA4 after script loads
      window.gtag('js', new Date());
      window.gtag('config', gaCode, {
        // Disable automatic page view tracking for SPA
        send_page_view: false,
        // Enable enhanced measurement
        enhanced_measurements: true
      });

      // Mark as ready for tracking
      this.isReady = true;
      console.log('Google Analytics 4 ready for tracking');

      // Send initial page view
      this.trackPageView();
    };

    script.onerror = () => {
      console.error('Failed to load Google Analytics script');
    };

    document.head.appendChild(script);
  }

  private setupNavigationTracking(): void {
    // Track page views on navigation for SPA
    this.router.events.pipe(
      filter(event => event instanceof NavigationEnd)
    ).subscribe((event: NavigationEnd) => {
      if (environment.production && window.gtag && this.isInitialized) {
        this.trackPageView(event.urlAfterRedirects);
      }
    });
  }

  private trackPageView(url?: string): void {
    if (!environment.production || !window.gtag || !environment.gaCode) return;

    const page_location = url ? `${window.location.origin}${url}` : window.location.href;
    const page_path = url || window.location.pathname;

    window.gtag('config', environment.gaCode, {
      page_path: page_path,
      page_location: page_location
    });
  }

  // Public methods for tracking events
  trackEvent(eventName: string, parameters?: any): void {
    if (!environment.production) {
      console.log(`[GA Debug] Event: ${eventName}`, parameters);
      return;
    }
    if (!window.gtag || !environment.gaCode || !this.isInitialized) return;

    window.gtag('event', eventName, parameters);
  }

  trackPurchase(transactionId: string, value: number, currency: string = 'USD', items?: any[]): void {
    if (!environment.production) {
      console.log(`[GA Debug] Purchase: ${transactionId}, Value: ${value} ${currency}`, items);
      return;
    }
    if (!window.gtag || !environment.gaCode || !this.isInitialized) return;

    window.gtag('event', 'purchase', {
      transaction_id: transactionId,
      value: value,
      currency: currency,
      items: items
    });
  }

  trackAddToCart(currency: string, value: number, items: any[]): void {
    if (!environment.production) {
      console.log(`[GA Debug] Add to Cart: Value: ${value} ${currency}`, items);
      return;
    }
    if (!window.gtag || !environment.gaCode || !this.isInitialized) return;

    window.gtag('event', 'add_to_cart', {
      currency: currency,
      value: value,
      items: items
    });
  }

  trackViewItem(currency: string, value: number, items: any[]): void {
    if (!environment.production) {
      console.log(`[GA Debug] View Item: Value: ${value} ${currency}`, items);
      return;
    }
    if (!window.gtag || !environment.gaCode || !this.isInitialized) return;

    window.gtag('event', 'view_item', {
      currency: currency,
      value: value,
      items: items
    });
  }

  trackBeginCheckout(currency: string, value: number, items: any[]): void {
    if (!environment.production) {
      console.log(`[GA Debug] Begin Checkout: Value: ${value} ${currency}`, items);
      return;
    }
    if (!window.gtag || !environment.gaCode || !this.isInitialized) return;

    window.gtag('event', 'begin_checkout', {
      currency: currency,
      value: value,
      items: items
    });
  }
}
