import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {CheckoutComponent} from "./checkout/checkout.component";
import {ContactComponent} from "./contact/contact.component";
import {OverviewComponent as ProductOverviewComponent} from './products/overview/overview.component';
import {OverviewComponent as ProductRentalsOverviewComponent} from './products/rentals/overview/overview.component';
import {CategoryComponent as ProductCategoryComponent} from './products/category/category.component';
import {DetailComponent as ProductDetailComponent} from './products/detail/detail.component';
import {PrivacyComponent} from "./privacy/privacy.component";

const routes: Routes = [
  { path: "", component: ProductOverviewComponent, data: { title: '' } },
  { path: 'product/:id', component: ProductDetailComponent, data: { title: '' } },
  { path: 'category/:name', component: ProductCategoryComponent, data: { title: '' } },
  { path: 'rentals', component: ProductRentalsOverviewComponent, data: { title: 'Rentals Overview' } },
  { path: 'checkout', component: CheckoutComponent, data: { title: 'Checkout' } },
  { path: 'contact', component: ContactComponent, data: { title: 'Contact Us' } },
  { path: 'privacy', component: PrivacyComponent, data: { title: 'Privacy Policy' } },
  { path: 'auth', loadChildren: () => import(`./auth/auth.module`).then(m => m.AuthModule), data: { title: 'Authentication' } },
  { path: 'account', loadChildren: () => import(`./account/account.module`).then(m => m.AccountModule), data: { title: 'Account' } },
  { path: 'admin', loadChildren: () => import(`./admin/admin.module`).then(m => m.AdminModule), data: { title: 'Admin' } },
];

@NgModule({
  imports: [RouterModule.forRoot(routes, {useHash: true, scrollPositionRestoration: 'enabled' })],
  exports: [RouterModule]
})
export class AppRoutingModule {
}
