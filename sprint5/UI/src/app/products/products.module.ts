// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ProductsRoutingModule} from './products-routing.module';
import {CategoryComponent} from './category/category.component';
import {OverviewComponent} from './rentals/overview/overview.component';
import {OverviewComponent as ProductOverviewComponent} from './overview/overview.component';
import {DetailComponent as ProductDetailComponent} from './detail/detail.component';
import {PaginationComponent} from "../pagination/pagination.component";
import {FormsModule, ReactiveFormsModule} from "@angular/forms";
import {NgxSliderModule} from "@angular-slider/ngx-slider";
import {TranslocoDirective} from "@jsverse/transloco";
import {FaIconComponent, FaIconLibrary, FontAwesomeModule} from "@fortawesome/angular-fontawesome";
import {
  faArrowsLeftRight,
  faArrowsUpDown,
  faBalanceScale,
  faFilter,
  faMinus,
  faPlus,
  faSearch,
  faShoppingCart,
  faStar
} from "@fortawesome/free-solid-svg-icons";
import {ComparisonComponent} from "./comparison/comparison.component";


@NgModule({
  imports: [
    CommonModule,
    ProductsRoutingModule,
    PaginationComponent,
    FormsModule,
    ReactiveFormsModule,
    NgxSliderModule,
    TranslocoDirective,
    FaIconComponent,
    FontAwesomeModule,
    ProductDetailComponent,
    CategoryComponent,
    OverviewComponent,
    ProductOverviewComponent,
    ComparisonComponent,
  ]
})
export class ProductsModule {
  constructor(library: FaIconLibrary) {
    library.addIcons(faFilter, faArrowsLeftRight, faArrowsUpDown, faSearch, faMinus, faPlus, faShoppingCart, faStar, faBalanceScale);
  }
}
