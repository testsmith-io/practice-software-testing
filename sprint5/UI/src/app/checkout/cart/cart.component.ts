import { Component, OnInit } from '@angular/core';
import { CartService } from "../../_services/cart.service";
import { CustomerAccountService } from "../../shared/customer-account.service";

@Component({
  selector: 'app-cart',
  templateUrl: './cart.component.html',
  styleUrls: ['./cart.component.css'] // Corrected 'styleUrl' to 'styleUrls'
})
export class CartComponent implements OnInit {

  items: any;
  isLoggedIn: boolean = false;
  total: number = 0;

  constructor(
    private cartService: CartService,
    private customerAccountService: CustomerAccountService
  ) {}

  ngOnInit(): void {
    this.fetchCartItems();
    this.isLoggedIn = this.customerAccountService.isLoggedIn();
  }

  fetchCartItems(): void {
    this.cartService.getItems().subscribe(items => {
      this.items = items;
      this.total = this.calculateTotal(items);
    });
  }

  updateQuantity(event: Event, item: any): void {
    const target = event.target as HTMLInputElement;
    const quantity = Math.max(1, parseInt(target.value, 10));

    if (quantity >= 1) {
      this.cartService.replaceQuantity(item.product.id, quantity).subscribe(() => {
        this.fetchCartItems();
      });
    }
  }

  delete(id: number): void {
    this.cartService.deleteItem(id).subscribe(() => {
      this.fetchCartItems();
    });
  }

  private calculateTotal(items: any[]): number {
    return items.reduce((sum, cartItem) => {
      const quantity = cartItem.quantity || 0;
      const price = cartItem.discount_percentage ? cartItem.discounted_price : cartItem.product?.price || 0;
      return sum + (quantity * price);
    }, 0);
  }
}
