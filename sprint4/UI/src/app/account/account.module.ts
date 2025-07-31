import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {OverviewComponent} from './overview/overview.component';
import {FavoritesComponent} from './favorites/favorites.component';
import {InvoicesComponent} from './invoices/invoices.component';
import {ProfileComponent} from './profile/profile.component';
import {RouterModule, Routes} from "@angular/router";
import {UserAuthGuard} from "../UserAuthGuard";
import {DetailsComponent as InvoiceDetails} from '../account/invoices/details/details.component';
import {ReactiveFormsModule} from "@angular/forms";
import {TruncatePipe} from "../_helpers/truncate.pipe";
import {MessagesComponent} from './messages/messages.component';
import {MessageDetailComponent} from './messages/message-detail/message-detail.component';
import {NgxPaginationModule} from "ngx-pagination";

const routes: Routes = [
  {path: '', component: OverviewComponent, canActivate: [UserAuthGuard]},
  {path: 'profile', component: ProfileComponent, canActivate: [UserAuthGuard]},
  {path: 'favorites', component: FavoritesComponent, canActivate: [UserAuthGuard]},
  {path: 'messages', component: MessagesComponent, canActivate: [UserAuthGuard]},
  {path: 'messages/:id', component: MessageDetailComponent, canActivate: [UserAuthGuard]},
  {path: 'invoices', component: InvoicesComponent, canActivate: [UserAuthGuard]},
  {path: 'invoices/:id', component: InvoiceDetails, canActivate: [UserAuthGuard]},
];

@NgModule({
  imports: [
    ReactiveFormsModule,
    CommonModule,
    RouterModule.forChild(routes),
    NgxPaginationModule,
    OverviewComponent,
    FavoritesComponent,
    InvoicesComponent,
    ProfileComponent,
    InvoiceDetails,
    TruncatePipe,
    MessagesComponent,
    MessageDetailComponent
  ],
  exports: [RouterModule]
})
export class AccountModule {
}
