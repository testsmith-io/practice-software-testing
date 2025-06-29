import {Component, inject, OnInit} from '@angular/core';
import {CartService} from "../../_services/cart.service";
import {CustomerAccountService} from "../../shared/customer-account.service";
import {ToastrService} from "ngx-toastr";
import {DecimalPipe, NgClass} from "@angular/common";
import {FaIconComponent} from "@fortawesome/angular-fontawesome";
import {ArchwizardModule} from "@y3krulez/angular-archwizard";
import {TranslocoDirective} from "@jsverse/transloco";

@Component({
  selector: 'app-cart',
  templateUrl: './cart.component.html',
  imports: [
    NgClass,
    DecimalPipe,
    FaIconComponent,
    ArchwizardModule,
    TranslocoDirective
  ],
  styleUrls: ['./cart.component.css']
})
export class CartComponent implements OnInit {
  private cartService = inject(CartService);
  private toastr = inject(ToastrService);
  private customerAccountService = inject(CustomerAccountService);

  cart: any;
  isLoggedIn: boolean = false;
  discount: number = 0;
  total: number = 0;
  subtotal: number = 0;

  ngOnInit(): void {
    this.fetchCartItems();
    this.isLoggedIn = this.customerAccountService.isLoggedIn();
  }

  fetchCartItems(): void {
    this.cartService.getCart().subscribe(cart => {
      this.cart = cart;
      this.total = this.calculateTotal(cart.cart_items);
      this.subtotal = this.total;
      this.discount = this.calculateDiscount(cart.additional_discount_percentage)
    });
  }

  updateQuantity(event: Event, item: any): void {
    const target = event.target as HTMLInputElement;
    const quantity = Math.max(1, parseInt(target.value, 10));

    if (quantity >= 1) {
      this.cartService.replaceQuantity(item.product.id, quantity).subscribe({
        next: () => {
          this.fetchCartItems();
          this.toastr.success('Product quantity updated.', null, { progressBar: true });
        },
        error: (response) => {
          this.toastr.error(response.error.message, null, { progressBar: true });
        }
      });
    }
  }

  delete(id: number): void {
    this.cartService.deleteItem(id).subscribe(() => {
      this.fetchCartItems();
      this.toastr.success('Product deleted.', null, {progressBar: true});
    });
  }

  private calculateTotal(items: any[]): number {
    return items.reduce((sum, cartItem) => {
      const quantity = cartItem.quantity || 0;
      const price = cartItem.discount_percentage ? cartItem.discounted_price : cartItem.product?.price || 0;
      return sum + (quantity * price);
    }, 0);
  }

  private calculateDiscount(percentage: number): number {
    // Calculate the discount amount
    const discountAmount = this.total * (percentage / 100);

    // Calculate the discounted price
    this.total = this.total - discountAmount;

    return discountAmount;
  }
}
