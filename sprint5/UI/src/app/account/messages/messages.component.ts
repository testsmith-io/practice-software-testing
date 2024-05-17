import {Component, OnInit} from '@angular/core';
import {first} from "rxjs/operators";
import {ContactMessage} from "../../models/contact-message";
import {ContactService} from "../../_services/contact.service";
import {Pagination} from "../../models/pagination";
import {DomSanitizer, SafeHtml} from "@angular/platform-browser";
import {TranslocoService} from "@jsverse/transloco";
import {Router} from "@angular/router";

@Component({
  selector: 'app-messages',
  templateUrl: './messages.component.html',
  styleUrls: ['./messages.component.css']
})
export class MessagesComponent implements OnInit {

  currentPage: number = 1;
  results: Pagination<ContactMessage>;
  noMessagesHtml: SafeHtml;

  constructor(private messageService: ContactService,
              private translocoService: TranslocoService,
              private sanitizer: DomSanitizer,
              private router: Router) {
  }

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
            window.location.href = '/#/auth/login';
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
