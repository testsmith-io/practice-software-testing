// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {ChangeDetectorRef, Component, inject, OnDestroy, OnInit} from '@angular/core';
import {CartService} from "../_services/cart.service";
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
  private readonly cartService = inject(CartService);
  private readonly changeDetectorRef = inject(ChangeDetectorRef);

  items: number;
  role: string = '';
  name: string = '';
  isLoggedIn: boolean;
  subscription: Subscription;

  constructor() {
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

  getCartItems(): number | undefined {
    const items = this.cartService.getItems();
    if (items?.length) {
      return items.map((item: { is_rental: number, quantity: number }) => (item.is_rental === 1) ? 1 : item.quantity).reduce((acc: number, item: number) => item + acc);
    }
    return undefined;
  }
}
