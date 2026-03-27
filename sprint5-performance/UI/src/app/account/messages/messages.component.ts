import {Component, inject, OnInit} from '@angular/core';
import {first} from "rxjs/operators";
import {ContactMessage} from "../../models/contact-message";
import {ContactService} from "../../_services/contact.service";
import {Pagination} from "../../models/pagination";
import {DomSanitizer, SafeHtml} from "@angular/platform-browser";
import {TranslocoDirective, TranslocoService} from "@jsverse/transloco";
import {Router, RouterLink} from "@angular/router";
import {RenderDelayDirective} from "../../render-delay-directive.directive";
import {TruncatePipe} from "../../_helpers/truncate.pipe";
import {NgClass} from "@angular/common";
import {PaginationComponent} from "../../pagination/pagination.component";

@Component({
  selector: 'app-messages',
  templateUrl: './messages.component.html',
  imports: [
    RenderDelayDirective,
    TruncatePipe,
    NgClass,
    RouterLink,
    PaginationComponent,
    TranslocoDirective
  ],
  styleUrls: []
})
export class MessagesComponent implements OnInit {
  private readonly messageService = inject(ContactService);
  private readonly translocoService = inject(TranslocoService);
  private readonly sanitizer = inject(DomSanitizer);
  private readonly router = inject(Router);

  currentPage: number = 1;
  results: Pagination<ContactMessage>;
  noMessagesHtml: SafeHtml;

  ngOnInit(): void {
    this.translocoService.selectTranslate('pages.my-account.messages.no-messages').subscribe((translation: string) => {
      this.noMessagesHtml = this.sanitizer.bypassSecurityTrustHtml(translation);
    });
    this.getMessages();
  }

  getMessages() {
    this.messageService.getMessages(this.currentPage)
      .pipe(first())
      .subscribe(
        (messages) => {
          this.results = messages
        },
        (error) => {
          if (error.status === 401 || error.status === 403) {
            window.localStorage.removeItem('TOKEN_KEY');
            window.location.href = '/auth/login';
          }
        }
      );
  }

  onPageChange(page: number) {
    // Handle page change here (e.g., fetch data for the selected page)
    this.currentPage = page;
    this.getMessages();
  }

  handleLinkClick(event: Event) {
    const target = event.target as HTMLElement;
    if (target && target.id === 'contact-link') {
      this.router.navigate(['/contact']);
    }
  }
}
