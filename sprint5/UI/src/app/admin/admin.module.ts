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
import {NgChartsModule} from 'ng2-charts';
import {SettingsComponent} from './settings/settings.component';
import {AverageSalesMonthComponent} from './reports/average-sales-month/average-sales-month.component';
import {AverageSalesWeekComponent} from './reports/average-sales-week/average-sales-week.component';
import {StatisticsComponent} from './reports/statistics/statistics.component';
import {PaginationComponent} from "../pagination/pagination.component";
import {SharedModule} from "../shared.module";

const routes: Routes = [
  { path: 'dashboard', component: DashboardComponent, canActivate: [AdminAuthGuard], data: { title: 'Dashboard' } },
  { path: 'brands', component: BrandsListComponent, canActivate: [AdminAuthGuard], data: { title: 'Brands List' } },
  { path: 'brands/add', component: BrandsAddEditComponent, canActivate: [AdminAuthGuard], data: { title: 'Add Brand' } },
  { path: 'brands/edit/:id', component: BrandsAddEditComponent, canActivate: [AdminAuthGuard], data: { title: 'Edit Brand' } },
  { path: 'categories', component: CategoriesListComponent, canActivate: [AdminAuthGuard], data: { title: 'Categories List' } },
  { path: 'categories/add', component: CategoriesAddEditComponent, canActivate: [AdminAuthGuard], data: { title: 'Add Category' } },
  { path: 'categories/edit/:id', component: CategoriesAddEditComponent, canActivate: [AdminAuthGuard], data: { title: 'Edit Category' } },
  { path: 'products', component: ProductsListComponent, canActivate: [AdminAuthGuard], data: { title: 'Products List' } },
  { path: 'products/add', component: ProductsAddEditComponent, canActivate: [AdminAuthGuard], data: { title: 'Add Product' } },
  { path: 'products/edit/:id', component: ProductsAddEditComponent, canActivate: [AdminAuthGuard], data: { title: 'Edit Product' } },
  { path: 'orders', component: OrdersListComponent, canActivate: [AdminAuthGuard], data: { title: 'Orders List' } },
  { path: 'orders/add', component: OrdersAddEditComponent, canActivate: [AdminAuthGuard], data: { title: 'Add Order' } },
  { path: 'orders/edit/:id', component: OrdersAddEditComponent, canActivate: [AdminAuthGuard], data: { title: 'Edit Order' } },
  { path: 'messages', component: MessagesListComponent, canActivate: [AdminAuthGuard], data: { title: 'Messages List' } },
  { path: 'messages/:id', component: MessageDetailComponent, canActivate: [AdminAuthGuard], data: { title: 'Message Detail' } },
  { path: 'users', component: UsersListComponent, canActivate: [AdminAuthGuard], data: { title: 'Users List' } },
  { path: 'users/add', component: UsersAddEditComponent, canActivate: [AdminAuthGuard], data: { title: 'Add User' } },
  { path: 'users/edit/:id', component: UsersAddEditComponent, canActivate: [AdminAuthGuard], data: { title: 'Edit User' } },
  { path: 'settings', component: SettingsComponent, canActivate: [AdminAuthGuard], data: { title: 'Settings' } },
  { path: 'reports/statistics', component: StatisticsComponent, canActivate: [AdminAuthGuard], data: { title: 'Statistics' } },
  { path: 'reports/average-sales-per-month', component: AverageSalesMonthComponent, canActivate: [AdminAuthGuard], data: { title: 'Average Sales Per Month' } },
  { path: 'reports/average-sales-per-week', component: AverageSalesWeekComponent, canActivate: [AdminAuthGuard], data: { title: 'Average Sales Per Week' } },
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
    NgChartsModule,
    RouterModule.forChild(routes),
    PaginationComponent,
    SharedModule
  ]
})
export class AdminModule {
}
