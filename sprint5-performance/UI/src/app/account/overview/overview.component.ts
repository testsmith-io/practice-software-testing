import {Component} from '@angular/core';
import {RouterLink} from "@angular/router";
import {FaIconComponent} from "@fortawesome/angular-fontawesome";
import {TranslocoDirective} from "@jsverse/transloco";

@Component({
  selector: 'app-overview',
  templateUrl: './overview.component.html',
  imports: [
    RouterLink,
    FaIconComponent,
    TranslocoDirective
  ],
  styleUrls: ['./overview.component.css']
})
export class OverviewComponent {

}
