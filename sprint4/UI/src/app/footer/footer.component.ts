// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Component} from '@angular/core';
import {environment} from "../../environments/environment";

@Component({
  selector: 'app-footer',
  templateUrl: './footer.component.html',
  styleUrls: ['./footer.component.css'],
})
export class FooterComponent {
  // The "Learn & Explore" section only appears on the deployed production build;
  // hidden in local/dev so the training environment stays neutral there.
  readonly isProduction = environment.production;
}
