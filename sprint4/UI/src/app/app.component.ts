import {Component} from '@angular/core';
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
export class AppComponent {

  public spinkit = Spinkit;
  title = 'Toolshop';

}
