import {ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {CartService} from "../_services/cart.service";
import {Subscription} from "rxjs";

@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.css']
})
export class HeaderComponent implements OnDestroy, OnInit {


  items: any;
  role: string = '';
  name: string = '';
  isLoggedIn: boolean;
  subscription: Subscription;

  constructor(private cartService: CartService,
              private changeDetectorRef: ChangeDetectorRef) {
    this.cartService.storageSub.subscribe(() => {
      this.items = this.getCartItems();
      this.changeDetectorRef.detectChanges();
    });
  }

  ngOnInit(): void {
    this.items = this.getCartItems();
  }

  ngOnDestroy() {
    this.subscription.unsubscribe();
  }

  getCartItems() {
    let items = this.cartService.getItems();
    if (items != null && items.length) {
      return items.map((item: { is_rental: number, quantity: number }) => (item.is_rental === 1) ? 1 : item.quantity).reduce((acc: any, item: any) => item + acc);
    }
  }
}
