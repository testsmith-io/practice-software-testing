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
