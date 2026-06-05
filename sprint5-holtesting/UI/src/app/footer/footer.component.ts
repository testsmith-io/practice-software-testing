// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Component} from '@angular/core';
import {RouterLink} from "@angular/router";
import {TranslocoDirective} from "@jsverse/transloco";

@Component({
  selector: 'app-footer',
  templateUrl: './footer.component.html',
  imports: [
    RouterLink,
    TranslocoDirective
  ],
  styleUrls: []
})
export class FooterComponent {

}
