import {Component, inject, OnInit} from '@angular/core';
import {ContactMessage} from "../../models/contact-message";
import {ContactService} from "../../_services/contact.service";
import {first} from "rxjs/operators";
import {Pagination} from "../../models/pagination";
import {NgClass} from "@angular/common";
import {NgxPaginationModule} from "ngx-pagination";
import {RouterLink} from "@angular/router";

@Component({
  selector: 'app-messages-list',
  templateUrl: './messages-list.component.html',
  imports: [
    NgClass,
    NgxPaginationModule,
    RouterLink
  ],
  styleUrls: []
})
export class MessagesListComponent implements OnInit {
  private readonly messageService = inject(ContactService);

  p: number = 1;
  results: Pagination<ContactMessage>;

  ngOnInit(): void {
    this.getMessages();
  }

  getMessages() {
    this.messageService.getMessages(this.p)
      .pipe(first())
      .subscribe((messages) => this.results = messages);
  }

  handlePageChange(event: number): void {
    this.p = event;
    this.getMessages();
  }

}
