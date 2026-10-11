// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';

const routes: Routes = [
  { path: "", loadChildren: () => import('./products/products.module').then(m => m.ProductsModule), data: { title: '', description: 'Browse the Toolshop catalog and practice software testing and test automation on a free, modern web application.' } },
  { path: 'privacy', loadChildren: () => import('./privacy/privacy.module').then(m => m.PrivacyModule), data: { title: 'Privacy Policy', description: 'Read the privacy policy for the Practice Software Testing Toolshop application.' } },
  { path: 'checkout', loadChildren: () => import('./checkout/checkout.module').then(m => m.CheckoutModule), data: { title: 'Checkout' } },
  { path: 'contact', loadChildren: () => import('./contact/contact.module').then(m => m.ContactModule), data: { title: 'Contact Us', description: 'Get in touch with the Practice Software Testing team.' } },
  { path: 'auth', loadChildren: () => import(`./auth/auth.module`).then(m => m.AuthModule), data: { title: 'Authentication' } },
  { path: 'account', loadChildren: () => import(`./account/account.module`).then(m => m.AccountModule), data: { title: 'Account' } },
  { path: 'admin', loadChildren: () => import(`./admin/admin.module`).then(m => m.AdminModule), data: { title: 'Admin' } },
];

@NgModule({
  imports: [RouterModule.forRoot(routes, {useHash: false, scrollPositionRestoration: 'enabled' })],
  exports: [RouterModule]
})
export class AppRoutingModule {
}
