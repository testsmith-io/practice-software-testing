import {Injectable} from '@angular/core';
import {map, Observable, Subject, switchMap, of} from "rxjs";
import {environment} from "../../environments/environment";
import {HttpClient} from "@angular/common/http";
import {catchError} from "rxjs/operators";

@Injectable({
  providedIn: 'root'
})
export class CartService {
  private apiURL = environment.apiUrl;
  public storageSub = new Subject<string>();

  constructor(private httpClient: HttpClient) {
  }

  private createCartAndStoreId(): Observable<string> {
    const location = JSON.parse(window.localStorage.getItem('GEO_LOCATION') || '{}');
    const requestBody: {lat:number, lng:number} | {} = location.lat && location.lng ? { lat: location.lat, lng: location.lng } : {};

    return this.httpClient.post(`${this.apiURL}/carts`, requestBody).pipe(
      switchMap((response: any) => {
        const cartId = response.id;
        sessionStorage.setItem('cart_id', cartId);
        return of(cartId);
      })
    );
  }

  private checkCartExistence(cartId: string): Observable<boolean> {
    return this.httpClient.get(`${this.apiURL}/carts/${cartId}`).pipe(
      map(() => true),
      catchError((error) => {
        if (error) {
          sessionStorage.removeItem('cart_quantity');
          sessionStorage.removeItem('cart_id');
          this.storageSub.next('changed');
          return of(false);
        } else {
          throw error;
        }
      })
    );
  }

  getOrCreateCartId(): Observable<string> {
    const cartId = sessionStorage.getItem('cart_id');

    if (cartId) {
      return this.checkCartExistence(cartId).pipe(
        switchMap((cartExists) => {
          if (cartExists) {
            return of(cartId);
          } else {
            return this.createCartAndStoreId();
          }
        })
      );
    } else {
      return this.createCartAndStoreId();
    }
  }

  getQuantity() {
    return JSON.parse(<string>sessionStorage.getItem('cart_quantity'))
  }

  private calculateCartQuantity(cartItems: any[]): number {
    return cartItems.reduce((totalQuantity, cartItem) => totalQuantity + cartItem.quantity, 0);
  }

  getCart(): Observable<any> {
    let cartId = sessionStorage.getItem('cart_id');

    return this.httpClient.get<any[]>(`${this.apiURL}/carts/${cartId}`).pipe(
      map((cartData: any) => {
        // Calculate the total cart quantity
        const cartQuantity = this.calculateCartQuantity(cartData.cart_items);

        // Store the cart_quantity in sessionStorage
        sessionStorage.setItem('cart_quantity', JSON.stringify(cartQuantity));
        this.storageSub.next('changed');

        return cartData;
      })
    );
  }

  addItem(item: any): Observable<any> {
    return this.getOrCreateCartId().pipe(
      switchMap((cartId) => {
        const currentQuantity = parseInt(sessionStorage.getItem('cart_quantity'), 10) || 0;
        const newQuantity = currentQuantity + item.quantity;
        sessionStorage.setItem('cart_quantity', JSON.stringify(newQuantity));
        this.storageSub.next('changed');
        return this.httpClient.post(`${this.apiURL}/carts/${cartId}`, {
          product_id: item.id,
          quantity: item.quantity,
        });
      })
    );
  }

  replaceQuantity(productId: number, quantity: number) {
    let cartId = sessionStorage.getItem('cart_id');
    return this.httpClient.put(this.apiURL + `/carts/${cartId}/product/quantity`, {
      product_id: productId,
      quantity: quantity
    });
  }

  deleteItem(productId: number) {
    let cartId = sessionStorage.getItem('cart_id');
    this.storageSub.next('changed');
    return this.httpClient.delete(this.apiURL + `/carts/${cartId}/product/${productId}`);
  }

  emptyCart() {
    let cartId = sessionStorage.getItem('cart_id');
    this.httpClient.delete(this.apiURL + `/carts/${cartId}`).subscribe(() => {
      sessionStorage.removeItem('cart_quantity');
      sessionStorage.removeItem('cart_id');
      this.storageSub.next('changed');
    });
  }

}
