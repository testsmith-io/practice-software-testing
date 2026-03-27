import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {CheckoutComponent} from "./checkout/checkout.component";
import {ContactComponent} from "./contact/contact.component";
import {OverviewComponent as ProductOverviewComponent} from './products/overview/overview.component';
import {OverviewComponent as ProductRentalsOverviewComponent} from './products/rentals/overview/overview.component';
import {CategoryComponent as ProductCategoryComponent} from './products/category/category.component';
import {DetailComponent as ProductDetailComponent} from './products/detail/detail.component';
import {PagenotfoundComponent} from "./shared/pagenotfound/pagenotfound.component";

const routes: Routes = [
  {path: "", component: ProductOverviewComponent},
  {path: 'product/:id', component: ProductDetailComponent},
  {path: 'category/:name', component: ProductCategoryComponent},
  {path: 'rentals', component: ProductRentalsOverviewComponent},
  {path: 'checkout', component: CheckoutComponent},
  {path: 'contact', component: ContactComponent},
  {path: 'auth', loadChildren: () => import(`./auth/auth.module`).then(m => m.AuthModule)},
  {path: 'account', loadChildren: () => import(`./account/account.module`).then(m => m.AccountModule)},
  {path: 'admin', loadChildren: () => import(`./admin/admin.module`).then(m => m.AdminModule)},
  { path: '**', pathMatch: 'full', component: PagenotfoundComponent }
];

@NgModule({
  imports: [RouterModule.forRoot(routes, {useHash: true, scrollPositionRestoration: 'enabled' })],
  exports: [RouterModule]
})
export class AppRoutingModule {
}
