import {Component, inject, OnInit} from '@angular/core';
import {CartService} from "../../_services/cart.service";
import {CustomerAccountService} from "../../shared/customer-account.service";
import {ToastrService} from "ngx-toastr";
import {DecimalPipe, NgClass} from "@angular/common";
import {FaIconComponent} from "@fortawesome/angular-fontawesome";
import {ArchwizardModule} from "@y3krulez/angular-archwizard";
import {TranslocoDirective} from "@jsverse/transloco";
import {Router} from "@angular/router";
import {GaService} from "../../_services/ga.service";

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
  private router = inject(Router);
  private gaService = inject(GaService);

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

  validateQuantityInput(event: Event, item: any): void {
    const target = event.target as HTMLInputElement;
    let value = parseInt(target.value, 10);

    // Check if value is NaN or exceeds maximum
    if (isNaN(value) || value < 1) {
      value = 1;
    } else if (value > 999999999) {
      value = 999999999;
    }

    target.value = value.toString();
  }

  updateQuantity(event: Event, item: any): void {
    const target = event.target as HTMLInputElement;
    let quantity = parseInt(target.value, 10);

    // Validate the quantity
    if (isNaN(quantity) || quantity < 1) {
      quantity = 1;
    } else if (quantity > 999999999) {
      quantity = 999999999;
    }

    target.value = quantity.toString();

    if (quantity >= 1 && quantity <= 999999999) {
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


  continueShopping(): void {
    this.router.navigate(['/']);
  }

  beginCheckout(): void {
    if (!this.cart?.cart_items?.length) return;

    const items = this.cart.cart_items.map((cartItem: any) => ({
      item_id: cartItem.product.id,
      item_name: cartItem.product.name,
      item_category: cartItem.product.category?.name || 'Unknown',
      item_brand: cartItem.product.brand?.name || 'Unknown',
      price: cartItem.discount_percentage ? cartItem.discounted_price : cartItem.product.price,
      quantity: cartItem.quantity
    }));

    this.gaService.trackBeginCheckout('USD', this.total, items);
  }
}
