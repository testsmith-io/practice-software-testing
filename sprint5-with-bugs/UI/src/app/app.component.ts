import {Component, inject, OnInit} from '@angular/core';
import {Spinkit} from "ng-http-loader";
import {faShoppingCart} from '@fortawesome/free-solid-svg-icons';
import {HeaderComponent} from "./header/header.component";
import {FaIconLibrary} from "@fortawesome/angular-fontawesome";
import {FooterComponent} from "./footer/footer.component";
import {RouterOutlet} from "@angular/router";
import {ToastsComponent} from "./_services/toasts.component";

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  imports: [
    HeaderComponent,
    FooterComponent,
    RouterOutlet,
    ToastsComponent
  ],
  styleUrls: []
})
export class AppComponent implements OnInit {
  private readonly library = inject(FaIconLibrary);
  public spinkit = Spinkit;
  title = 'Toolshop';

  constructor() {
    this.library.addIcons(faShoppingCart);
  }

  ngOnInit(): void {
    if (!window.sessionStorage.getItem('GEO_LOCATION') &&
      window.sessionStorage.getItem('RETRIEVE_GEOLOCATION')) {
      this.getLocation();
    }
  }

  getLocation(): void {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition((position) => {
        window.sessionStorage.setItem("GEO_LOCATION", JSON.stringify({
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
