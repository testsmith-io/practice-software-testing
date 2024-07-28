import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { ProductsRoutingModule } from './products-routing.module';
import { ProductsComponent } from './products.component';
import {CategoryComponent} from './category/category.component';
import {OverviewComponent} from './rentals/overview/overview.component';
import {OverviewComponent as ProductOverviewComponent} from './overview/overview.component';
import {DetailComponent as ProductDetailComponent} from './detail/detail.component';
import {PaginationComponent} from "./../pagination/pagination.component";
import {FormsModule, ReactiveFormsModule} from "@angular/forms";
import {NgxSliderModule} from "@angular-slider/ngx-slider";
import {TranslocoDirective} from "@jsverse/transloco";


@NgModule({
  declarations: [
    ProductsComponent,
    ProductDetailComponent,
    CategoryComponent,
    OverviewComponent,
    ProductOverviewComponent,
  ],
  imports: [
    CommonModule,
    ProductsRoutingModule,
    PaginationComponent,
    FormsModule,
    ReactiveFormsModule,
    NgxSliderModule,
    TranslocoDirective
  ]
})
export class ProductsModule { }
