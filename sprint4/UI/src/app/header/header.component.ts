import {ChangeDetectorRef, Component, inject, OnDestroy, OnInit} from '@angular/core';
import {CartService} from "../_services/cart.service";
import {CustomerAccountService} from "../shared/customer-account.service";
import {Subscription} from "rxjs";
import {RouterLink} from "@angular/router";


@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  imports: [
    RouterLink
],
  styleUrls: ['./header.component.css']
})
export class HeaderComponent implements OnDestroy, OnInit {
  private readonly auth = inject(CustomerAccountService);
  private readonly cartService = inject(CartService);
  private readonly changeDetectorRef = inject(ChangeDetectorRef);

  items: any;
  role: string = '';
  name: string = '';
  isLoggedIn: boolean;
  subscription: Subscription;

  constructor() {
    this.cartService.storageSub.subscribe(() => {
      this.items = this.getCartItems();
      this.changeDetectorRef.detectChanges();
    })
    this.subscription = this.auth.authSub.subscribe(loggedIn => {
      if (loggedIn) {
        this.isLoggedIn = true;
        this.getSignedInUser();
      } else {
        this.name = '';
        this.role = '';
      }
    });
  }

  ngOnInit(): void {
    this.items = this.getCartItems();
    this.role = this.auth.getRole();
    this.getSignedInUser();
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

  getSignedInUser() {
    this.auth.getDetails().subscribe(res => {
      this.role = this.auth.getRole();
      this.name = res.first_name + ' ' + res.last_name;
    })
  }

  logout() {
    this.auth.logout();
    window.location.reload();
  }

}
