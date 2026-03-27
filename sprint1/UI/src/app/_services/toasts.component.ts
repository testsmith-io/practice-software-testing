import {Component, inject, TemplateRef} from '@angular/core';

import {NgbToast} from "@ng-bootstrap/ng-bootstrap";
import {NgTemplateOutlet} from "@angular/common";
import {ToastService} from "./toast.service";

@Component({
  selector: 'app-toasts',
  template: `
    @for (toast of toastService.toasts; track toast) {
      <ngb-toast
        [class]="toast.classname"
        [autohide]="true"
        [delay]="toast.delay || 3000"
        (hidden)="toastService.remove(toast)"
        >
        @if (isTemplate(toast)) {
          <ng-template [ngTemplateOutlet]="toast.textOrTpl"></ng-template>
        } @else {
          {{ toast.textOrTpl }}
        }
      </ngb-toast>
    }
    `,
  imports: [
    NgbToast,
    NgTemplateOutlet
],
  host: {'class': 'toast-container position-fixed top-0 end-0 p-3', 'style': 'z-index: 1200'}
})
export class ToastsComponent {
  toastService = inject(ToastService);

  isTemplate(toast: any) {
    return toast.textOrTpl instanceof TemplateRef;
  }
}
