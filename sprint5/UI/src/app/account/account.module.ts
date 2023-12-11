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
import {PaginationComponent} from "../pagination/pagination.component";
import {SharedModule} from "../shared.module";

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
  declarations: [
    OverviewComponent,
    FavoritesComponent,
    InvoicesComponent,
    ProfileComponent,
    InvoiceDetails,
    TruncatePipe,
    MessagesComponent,
    MessageDetailComponent
  ],
  imports: [
    ReactiveFormsModule,
    CommonModule,
    RouterModule.forChild(routes),
    PaginationComponent,
    SharedModule
  ],
  exports: [RouterModule]
})
export class AccountModule {
}
