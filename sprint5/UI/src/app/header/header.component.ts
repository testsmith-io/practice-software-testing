import {ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {CartService} from "../_services/cart.service";
import {CustomerAccountService} from "../shared/customer-account.service";
import {Subscription} from "rxjs";
import {TranslocoService} from "@jsverse/transloco";

@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.css']
})
export class HeaderComponent implements OnDestroy, OnInit {

  activeLanguage: string;
  items: any;
  role: string = '';
  name: string = '';
  isLoggedIn: boolean;
  subscription: Subscription;

  constructor(private auth: CustomerAccountService,
              private cartService: CartService,
              private changeDetectorRef: ChangeDetectorRef,
              private translocoService: TranslocoService) {
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
    this.activeLanguage = this.translocoService.getActiveLang();
    this.getSignedInUser();
  }

  ngOnDestroy() {
    this.subscription.unsubscribe();
  }

  getCartItems() {
    return this.cartService.getQuantity();
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

  changeSiteLanguage(language: string): void {
    this.translocoService.setActiveLang(language);
    localStorage.setItem('language', language);
    this.activeLanguage = language;
  }

}
