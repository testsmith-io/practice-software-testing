import {Component} from '@angular/core';
import {FaIconComponent} from "@fortawesome/angular-fontawesome";
import {RouterLink} from "@angular/router";
import {TranslocoDirective} from "@jsverse/transloco";

@Component({
  selector: 'app-overview',
  templateUrl: './overview.component.html',
  imports: [
    FaIconComponent,
    RouterLink,
    TranslocoDirective
  ],
  styleUrls: ['./overview.component.css']
})
export class OverviewComponent {

}
