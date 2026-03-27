import {inject, Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {environment} from '../../environments/environment';
import {Observable, of, Subject, throwError} from 'rxjs';
import {catchError, map, switchMap, tap} from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class CartService {
  private readonly httpClient = inject(HttpClient);
  private readonly apiURL = `${environment.apiUrl}/carts`;
  public storageSub = new Subject<string>();

  private createCartAndStoreId(): Observable<string> {
    const location = this.getGeoLocation();
    const requestBody = location.lat && location.lng ? { lat: location.lat, lng: location.lng } : {};

    return this.httpClient.post<{ id: string }>(this.apiURL, requestBody).pipe(
      map(response => {
        const cartId = response.id;
        sessionStorage.setItem('cart_id', cartId);
        return cartId;
      })
    );
  }

  private checkCartExistence(cartId: string): Observable<boolean> {
    return this.httpClient.get(`${this.apiURL}/${cartId}`).pipe(
      map(() => true),
      catchError(error => {
        sessionStorage.removeItem('cart_quantity');
        sessionStorage.removeItem('cart_id');
        this.storageSub.next('changed');
        return of(false);
      })
    );
  }

  getOrCreateCartId(): Observable<string> {
    const cartId = sessionStorage.getItem('cart_id');
    return cartId
      ? this.checkCartExistence(cartId).pipe(
        switchMap(exists => (exists ? of(cartId) : this.createCartAndStoreId()))
      )
      : this.createCartAndStoreId();
  }

  getQuantity(): number {
    try {
      return JSON.parse(sessionStorage.getItem('cart_quantity') || '0');
    } catch {
      return 0;
    }
  }

  private calculateCartQuantity(cartItems: any[]): number {
    return cartItems.reduce((total, item) => total + item.quantity, 0);
  }

  getCart(): Observable<any> {
    const cartId = sessionStorage.getItem('cart_id');
    if (!cartId) return of(null);

    return this.httpClient.get<any>(`${this.apiURL}/${cartId}`).pipe(
      tap(cart => {
        const quantity = this.calculateCartQuantity(cart.cart_items || []);
        sessionStorage.setItem('cart_quantity', JSON.stringify(quantity));
        this.storageSub.next('changed');
      })
    );
  }

  addItem(item: any): Observable<any> {
    return this.getOrCreateCartId().pipe(
      switchMap(cartId =>
        this.httpClient.post(`${this.apiURL}/${cartId}`, {
          product_id: item.id,
          quantity: item.quantity
        }).pipe(
          tap(() => {
            const current = this.getQuantity();
            sessionStorage.setItem('cart_quantity', JSON.stringify(current + item.quantity));
            this.storageSub.next('changed');
          })
        )
      )
    );
  }

  replaceQuantity(productId: number, quantity: number): Observable<any> {
    const cartId = sessionStorage.getItem('cart_id');
    if (!cartId) return throwError(() => new Error('No cart ID'));
    return this.httpClient.put(`${this.apiURL}/${cartId}/product/quantity`, { product_id: productId, quantity });
  }

  deleteItem(productId: number): Observable<any> {
    const cartId = sessionStorage.getItem('cart_id');
    if (!cartId) return throwError(() => new Error('No cart ID'));
    this.storageSub.next('changed');
    return this.httpClient.delete(`${this.apiURL}/${cartId}/product/${productId}`);
  }

  emptyCart(): void {
    const cartId = sessionStorage.getItem('cart_id');
    if (!cartId) return;

    this.httpClient.delete(`${this.apiURL}/${cartId}`).subscribe({
      next: () => this.clearCart(),
      error: () => this.clearCart() // clear anyway
    });
  }

  // 🔒 Private helpers

  private getGeoLocation(): { lat?: number; lng?: number } {
    try {
      return JSON.parse(localStorage.getItem('GEO_LOCATION') || '{}');
    } catch {
      return {};
    }
  }

  private clearCart(): void {
    sessionStorage.removeItem('cart_quantity');
    sessionStorage.removeItem('cart_id');
    this.storageSub.next('changed');
  }
}
