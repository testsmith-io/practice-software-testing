// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {ContactComponent} from "./contact/contact.component";
import {OverviewComponent as ProductOverviewComponent} from './products/overview/overview.component';
import {CategoryComponent as ProductCategoryComponent} from './products/category/category.component';
import {DetailComponent as ProductDetailComponent} from './products/detail/detail.component';

const routes: Routes = [
  {path: "", component: ProductOverviewComponent, data: {description: 'Browse the Toolshop catalog and practice software testing and test automation on a free, modern web application.'}},
  {path: 'product/:id', component: ProductDetailComponent},
  {path: 'category/:name', component: ProductCategoryComponent},
  {path: 'contact', component: ContactComponent, data: {description: 'Get in touch with the Practice Software Testing team.'}},
];

@NgModule({
  imports: [RouterModule.forRoot(routes, {useHash: true})],
  exports: [RouterModule]
})
export class AppRoutingModule {
}
