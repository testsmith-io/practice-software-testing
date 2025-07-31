import {Component, inject, OnInit} from '@angular/core';
import {first} from "rxjs/operators";
import {ContactMessage} from "../../models/contact-message";
import {ContactService} from "../../_services/contact.service";
import {Pagination} from "../../models/pagination";
import {RouterLink} from "@angular/router";
import {NgxPaginationModule} from "ngx-pagination";
import {TruncatePipe} from "../../_helpers/truncate.pipe";
import {NgClass} from "@angular/common";

@Component({
  selector: 'app-messages',
  templateUrl: './messages.component.html',
  imports: [
    RouterLink,
    NgxPaginationModule,
    TruncatePipe,
    NgClass
  ],
  styleUrls: []
})
export class MessagesComponent implements OnInit {
  private readonly messageService = inject(ContactService);
  p: number = 1;
  results: Pagination<ContactMessage>;

  ngOnInit(): void {
    this.getMessages();
  }

  getMessages() {
    this.messageService.getMessages(this.p)
      .pipe(first())
      .subscribe((messages) => {
          this.results = messages
        },
        (error) => {
          if (error.status === 401 || error.status === 403) {
            window.localStorage.removeItem('TOKEN_KEY');
            window.location.href = '/#/auth/login';
          }
        });
  }

  handlePageChange(event: number): void {
    this.p = event;
    this.getMessages();
  }

}
