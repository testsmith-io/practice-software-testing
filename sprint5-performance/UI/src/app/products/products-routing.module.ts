import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {OverviewComponent as ProductOverviewComponent} from './overview/overview.component';
import {DetailComponent as ProductDetailComponent} from './detail/detail.component';
import {OverviewComponent as RentalOverviewComponent} from './rentals/overview/overview.component';
import {CategoryComponent as ProductCategoryComponent} from './category/category.component';

const routes: Routes = [
  { path: '', component: ProductOverviewComponent, data: { title: '' } },
  { path: 'product/:id', component: ProductDetailComponent, data: { title: '' } },
  { path: 'category/:name', component: ProductCategoryComponent, data: { title: '' } },
  { path: 'rentals', component: RentalOverviewComponent, data: { title: 'Rentals Overview' } },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class ProductsRoutingModule { }
