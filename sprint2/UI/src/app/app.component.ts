import {Component, OnInit} from '@angular/core';
import {Spinkit} from "ng-http-loader";
import {HeaderComponent} from "./header/header.component";
import {RouterOutlet} from "@angular/router";
import {FooterComponent} from "./footer/footer.component";
import {ToastsComponent} from "./_services/toasts.component";

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
