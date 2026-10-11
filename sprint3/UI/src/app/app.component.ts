// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Component, inject, OnInit} from '@angular/core';
import {Spinkit} from "ng-http-loader";
import {HeaderComponent} from "./header/header.component";
import {ActivatedRoute, NavigationEnd, Router, RouterOutlet} from "@angular/router";
import {FooterComponent} from "./footer/footer.component";
import {ToastsComponent} from "./_services/toasts.component";
import {filter, map} from 'rxjs/operators';
import {SeoService} from './_services/seo.service';

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

  private readonly seoService = inject(SeoService);
  private readonly router = inject(Router);
  private readonly activatedRoute = inject(ActivatedRoute);

  ngOnInit(): void {
    this.router.events.pipe(
      filter(event => event instanceof NavigationEnd),
      map(() => this.activatedRoute),
      map(route => {
        while (route.firstChild) route = route.firstChild;
        return route;
      }),
      filter(route => route.outlet === 'primary'),
      map(route => route.snapshot.data)
    ).subscribe(data => {
      this.seoService.setDescription(data['description']);
      this.seoService.setCanonicalUrl(this.router.url);
    });
  }

}
