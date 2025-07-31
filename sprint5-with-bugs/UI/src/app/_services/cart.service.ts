import {inject, Injectable} from '@angular/core';
import {Subject} from "rxjs";
import {ToastService} from "./toast.service";

@Injectable({
  providedIn: 'root'
})
export class CartService {
  private toastService = inject(ToastService);
  public storageSub = new Subject<string>();

  getItems() {
    return JSON.parse(<string>sessionStorage.getItem('cart'))
  }

  addItem(item: any) {
    if (sessionStorage.getItem('cart') == null) {
      let items: any = [];
      items[0] = item;
      sessionStorage.setItem('cart', JSON.stringify(items));
    } else {
      let itemsFromStorage = JSON.parse(<string>sessionStorage.getItem('cart'));

      if (item.name === 'Thor Hammer') {
        let thorHammerInCart = itemsFromStorage.find((p: { name: string; }) => p.name === 'Thor Hammer');

        if (thorHammerInCart) {
          this.toastService.show('You can only have one Thor Hammer in the cart.', {classname: 'bg-danger text-light'})
          return;
        }
      }

      let itemFound = itemsFromStorage.find((p: { id: any; }) => p.id == item.id);

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

    let item = itemsFromStorage.find((item: { id: number }) => item.id === id);

    if (item && item.name === 'Thor Hammer' && quantity > 1) {
      this.toastService.show('You can only have one Thor Hammer in the cart.', {classname: 'bg-danger text-light'})
      return;
    }

    itemsFromStorage = itemsFromStorage.map((item: { quantity: number; id: number; total: number; price: number }) => {
      if (item.id === id) {
        item.quantity = quantity;
      }
      item.total = item.quantity * item.price;
      return item;
    });

    sessionStorage.setItem('cart', JSON.stringify(itemsFromStorage));
    this.storageSub.next('changed');
  }


  updateQuantity(id: number, quantity: number) {
    let itemsFromStorage = JSON.parse(<string>sessionStorage.getItem('cart'));

    let item = itemsFromStorage.find((item: { id: number }) => item.id === id);

    if (item && item.name === 'Thor Hammer' && quantity > 1) {
      this.toastService.show('You can only have one Thor Hammer in the cart.', {classname: 'bg-danger text-light'})
      return;
    }

    itemsFromStorage = itemsFromStorage.map((item: { quantity: number; id: number; total: number; price: number }) => {
      if (item.id === id) {
        item.quantity = quantity;
      }
      item.total = item.quantity * item.price;
      return item;
    });

    sessionStorage.setItem('cart', JSON.stringify(itemsFromStorage));
    this.storageSub.next('changed');
  }


  deleteItem(id: number) {
    let itemsFromStorage = JSON.parse(<string>sessionStorage.getItem('cart'));
    itemsFromStorage = itemsFromStorage.filter((item: { id: number; }) => item.id != id);
    sessionStorage.setItem('cart', JSON.stringify(itemsFromStorage));
    this.storageSub.next('changed');
  }

  isItemInCart(id: number) {
    let itemsFromStorage = JSON.parse(<string>sessionStorage.getItem('cart'));
    return (itemsFromStorage) ? itemsFromStorage.find((p: { id: number; }) => p.id == id) : false;
  }

  getQuantityFromItemInCart(id: number) {
    let itemsFromStorage = JSON.parse(<string>sessionStorage.getItem('cart'));
    return (itemsFromStorage) ? itemsFromStorage.find((p: { id: number; }) => p.id == id)?.quantity : 1;
  }

  emptyCart() {
    sessionStorage.removeItem('cart');
    this.storageSub.next('changed');
  }
}
