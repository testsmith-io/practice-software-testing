import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {RouterModule, Routes} from "@angular/router";
import {ReactiveFormsModule} from "@angular/forms";
import {AdminAuthGuard} from "../AdminAuthGuard";
import {DashboardComponent} from './dashboard/dashboard.component';
import {BrandsListComponent} from './brands-list/brands-list.component';
import {BrandsAddEditComponent} from './brands-add-edit/brands-add-edit.component';
import {CategoriesAddEditComponent} from './categories-add-edit/categories-add-edit.component';
import {CategoriesListComponent} from './categories-list/categories-list.component';
import {OrdersListComponent} from './orders-list/orders-list.component';
import {ProductsListComponent} from './products-list/products-list.component';
import {UsersListComponent} from './users-list/users-list.component';
import {MessagesListComponent} from './messages-list/messages-list.component';
import {MessageDetailComponent} from './message-detail/message-detail.component';
import {UsersAddEditComponent} from './users-add-edit/users-add-edit.component';
import {ProductsAddEditComponent} from './products-add-edit/products-add-edit.component';
import {OrdersAddEditComponent} from './orders-add-edit/orders-add-edit.component';
import {ChartModule} from "angular2-chartjs";
import {SettingsComponent} from './settings/settings.component';
import {NgxPaginationModule} from "ngx-pagination";
import {AverageSalesMonthComponent} from './reports/average-sales-month/average-sales-month.component';
import {AverageSalesWeekComponent} from './reports/average-sales-week/average-sales-week.component';
import {StatisticsComponent} from './reports/statistics/statistics.component';

const routes: Routes = [
  {path: 'dashboard', component: DashboardComponent, canActivate: [AdminAuthGuard]},
  {path: 'brands', component: BrandsListComponent, canActivate: [AdminAuthGuard]},
  {path: 'brands/add', component: BrandsAddEditComponent, canActivate: [AdminAuthGuard]},
  {path: 'brands/edit/:id', component: BrandsAddEditComponent, canActivate: [AdminAuthGuard]},
  {path: 'categories', component: CategoriesListComponent, canActivate: [AdminAuthGuard]},
  {path: 'categories/add', component: CategoriesAddEditComponent, canActivate: [AdminAuthGuard]},
  {path: 'categories/edit/:id', component: CategoriesAddEditComponent, canActivate: [AdminAuthGuard]},
  {path: 'products', component: ProductsListComponent, canActivate: [AdminAuthGuard]},
  {path: 'products/add', component: ProductsAddEditComponent, canActivate: [AdminAuthGuard]},
  {path: 'products/edit/:id', component: ProductsAddEditComponent, canActivate: [AdminAuthGuard]},
  {path: 'orders', component: OrdersListComponent, canActivate: [AdminAuthGuard]},
  {path: 'orders/add', component: OrdersAddEditComponent, canActivate: [AdminAuthGuard]},
  {path: 'orders/edit/:id', component: OrdersAddEditComponent, canActivate: [AdminAuthGuard]},
  {path: 'messages', component: MessagesListComponent, canActivate: [AdminAuthGuard]},
  {path: 'messages/:id', component: MessageDetailComponent, canActivate: [AdminAuthGuard]},
  {path: 'users', component: UsersListComponent, canActivate: [AdminAuthGuard]},
  {path: 'users/add', component: UsersAddEditComponent, canActivate: [AdminAuthGuard]},
  {path: 'users/edit/:id', component: UsersAddEditComponent, canActivate: [AdminAuthGuard]},
  {path: 'settings', component: SettingsComponent, canActivate: [AdminAuthGuard]},
  {path: 'reports/statistics', component: StatisticsComponent, canActivate: [AdminAuthGuard]},
  {path: 'reports/average-sales-per-month', component: AverageSalesMonthComponent, canActivate: [AdminAuthGuard]},
  {path: 'reports/average-sales-per-week', component: AverageSalesWeekComponent, canActivate: [AdminAuthGuard]},
];

@NgModule({
  declarations: [
    DashboardComponent,
    BrandsListComponent,
    BrandsAddEditComponent,
    CategoriesAddEditComponent,
    CategoriesListComponent,
    OrdersListComponent,
    ProductsListComponent,
    UsersListComponent,
    UsersAddEditComponent,
    ProductsAddEditComponent,
    OrdersAddEditComponent,
    SettingsComponent,
    AverageSalesMonthComponent,
    AverageSalesWeekComponent,
    MessagesListComponent,
    MessageDetailComponent,
    StatisticsComponent
  ],
    imports: [
        ReactiveFormsModule,
        CommonModule,
        ChartModule,
        RouterModule.forChild(routes),
        NgxPaginationModule,
        ReactiveFormsModule
    ]
})
export class AdminModule {
}
