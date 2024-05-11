import {Component, OnInit} from '@angular/core';
import {Spinkit} from "ng-http-loader";
import {GaService} from "./_services/ga.service";
import { Router, NavigationEnd, ActivatedRoute } from '@angular/router';
import { Title } from '@angular/platform-browser';
import { filter, map, mergeMap, switchMap } from 'rxjs/operators';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent implements OnInit {

  public spinkit = Spinkit;
  title = 'Toolshop';

  constructor(private gaService: GaService,
              private titleService: Title,
              private router: Router,
              private activatedRoute: ActivatedRoute) {
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
