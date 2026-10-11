// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Component, inject, OnInit} from '@angular/core';
import {Spinkit} from "ng-http-loader";
import {HeaderComponent} from "./header/header.component";
import {NavigationEnd, Router, RouterOutlet} from "@angular/router";
import {filter} from "rxjs/operators";
import {FooterComponent} from "./footer/footer.component";
import {ToastsComponent} from "./_services/toasts.component";
import {SeoService} from "./_services/seo.service";

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  imports: [
    HeaderComponent,
    RouterOutlet,
    FooterComponent,
    ToastsComponent
  ],
  styleUrls: []
})
export class AppComponent implements OnInit {

  public spinkit = Spinkit;
  title = 'Toolshop';

  private readonly router = inject(Router);
  private readonly seoService = inject(SeoService);

  ngOnInit(): void {
    this.router.events.pipe(
      filter(event => event instanceof NavigationEnd)
    ).subscribe(() => {
      this.seoService.setCanonicalUrl(this.router.url);
    });
  }

}
