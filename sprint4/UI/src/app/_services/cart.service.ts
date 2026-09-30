// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Injectable} from '@angular/core';
import {Subject} from "rxjs";
import {CartItem} from "../models/cart";

@Injectable({
  providedIn: 'root'
})
export class CartService {
  public storageSub = new Subject<string>();

  getItems() {
    return JSON.parse(<string>sessionStorage.getItem('cart'))
  }

  addItem(item: CartItem) {
    if (sessionStorage.getItem('cart') == null) {
      const items: CartItem[] = [];
      items[0] = item;
      sessionStorage.setItem('cart', JSON.stringify(items));
    } else {
      const itemsFromStorage = JSON.parse(<string>sessionStorage.getItem('cart'));
      const itemFound = itemsFromStorage.some((p: CartItem) => p.id === item.id);
      if (!itemFound) {
        itemsFromStorage.push(item);
        sessionStorage.setItem('cart', JSON.stringify(itemsFromStorage));
      } else {
        this.updateQuantity(item.id, item.quantity);
      }
    }
    this.storageSub.next('changed');
  }

  replaceQuantity(id: number, quantity: number) {
    let itemsFromStorage = JSON.parse(<string>sessionStorage.getItem('cart'));
    itemsFromStorage = itemsFromStorage.map((item: { quantity: number; id: number; total: number; price: number }) => {
        if (item.id === id) {
          item.quantity = quantity;
        }
        item.total = item.quantity * item.price;
        return item;
      }
    );
    sessionStorage.setItem('cart', JSON.stringify(itemsFromStorage));
    this.storageSub.next('changed');
  }

  updateQuantity(id: number, quantity: number) {
    let itemsFromStorage = JSON.parse(<string>sessionStorage.getItem('cart'));
    itemsFromStorage = itemsFromStorage.map((item: { quantity: number; id: number; total: number; price: number }) => {
        if (item.id === id) {
          item.quantity += quantity;
        }
        item.total = item.quantity * item.price;
        return item;
      }
    );
    sessionStorage.setItem('cart', JSON.stringify(itemsFromStorage));
    this.storageSub.next('changed');
  }

  deleteItem(id: number) {
    let itemsFromStorage = JSON.parse(<string>sessionStorage.getItem('cart'));
    itemsFromStorage = itemsFromStorage.filter((item: { id: number; }) => item.id !== id);
    sessionStorage.setItem('cart', JSON.stringify(itemsFromStorage));
    this.storageSub.next('changed');
  }

  isItemInCart(id: number) {
    const itemsFromStorage = JSON.parse(<string>sessionStorage.getItem('cart'));
    return (itemsFromStorage) ? itemsFromStorage.find((p: { id: number; }) => p.id === id) : false;
  }

  getQuantityFromItemInCart(id: number) {
    const itemsFromStorage = JSON.parse(<string>sessionStorage.getItem('cart'));
    return (itemsFromStorage) ? itemsFromStorage.find((p: { id: number; }) => p.id === id)?.quantity : 1;
  }

  emptyCart() {
    sessionStorage.removeItem('cart');
    this.storageSub.next('changed');
  }
}
