import {Component, OnInit} from '@angular/core';
import {CartService} from "../../_services/cart.service";
import {CustomerAccountService} from "../../shared/customer-account.service";
import {ToastrService} from "ngx-toastr";

@Component({
  selector: 'app-cart',
  templateUrl: './cart.component.html',
  styleUrls: ['./cart.component.css'] // Corrected 'styleUrl' to 'styleUrls'
})
export class CartComponent implements OnInit {

  cart: any;
  isLoggedIn: boolean = false;
  discount: number = 0;
  total: number = 0;
  subtotal: number = 0;

  constructor(
    private cartService: CartService,
    private toastr: ToastrService,
    private customerAccountService: CustomerAccountService
  ) {
  }

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
      this.cartService.replaceQuantity(item.product.id, quantity).subscribe(() => {
        this.fetchCartItems();
        this.toastr.success('Product quantity updated.', null, {progressBar: true});
      }, (response) => {
        this.toastr.error(response.error.message, null, {progressBar: true});
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
