// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {PrivacyComponent} from './privacy.component';

const routes: Routes = [{ path: '', component: PrivacyComponent }];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class PrivacyRoutingModule { }
