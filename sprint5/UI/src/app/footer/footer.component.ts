// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Component, VERSION} from '@angular/core';
import {RouterLink} from "@angular/router";
import {TranslocoDirective} from "@jsverse/transloco";
import {appVersion} from "../../environments/version";
import {environment} from "../../environments/environment";

@Component({
  selector: 'app-footer',
  templateUrl: './footer.component.html',
  styleUrls: ['./footer.component.css'],
  imports: [
    RouterLink,
    TranslocoDirective
  ],
})
export class FooterComponent {
  readonly version = appVersion;
  readonly angularVersion = VERSION.full;
  // The "Learn & Explore" section only appears on the deployed production build;
  // hidden in local/dev so the training environment stays neutral there.
  readonly isProduction = environment.production;
}
