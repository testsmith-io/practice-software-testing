import {Component, inject, OnInit} from '@angular/core';
import {ActivatedRoute, NavigationEnd, Router, RouterOutlet} from '@angular/router';
import {Title} from '@angular/platform-browser';
import {filter, map} from 'rxjs/operators';
import {HeaderComponent} from "./header/header.component";
import {FooterComponent} from "./footer/footer.component";
import {faGlobe, faShoppingCart} from '@fortawesome/free-solid-svg-icons';
import {FaIconLibrary} from '@fortawesome/angular-fontawesome';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  imports: [
    HeaderComponent,
    RouterOutlet,
    FooterComponent
  ],
  styleUrls: []
})
export class AppComponent implements OnInit {
  private readonly library = inject(FaIconLibrary);
  private readonly titleService = inject(Title);
  private readonly router = inject(Router);
  private readonly activatedRoute = inject(ActivatedRoute);
  title = 'Toolshop';

  constructor() {
    this.library.addIcons(faGlobe, faShoppingCart);
  }
  ngOnInit(): void {
    if (!window.localStorage.getItem('GEO_LOCATION') &&
      window.localStorage.getItem('RETRIEVE_GEOLOCATION')) {
      this.getLocation();
    }
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
      const baseTitle = 'Practice Software Testing - Toolshop - v5.0';
      const title = data['title'] ? `${data['title']} - ${baseTitle}` : baseTitle;
      this.titleService.setTitle(title);
    });
  }

  getLocation(): void {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition((position) => {
        window.localStorage.setItem("GEO_LOCATION", JSON.stringify({
          lng: position.coords.longitude,
          lat: position.coords.latitude
        }));
        window.location.href = '/';
      });
    } else {
      console.log("No support for geolocation")
    }
  }

}
