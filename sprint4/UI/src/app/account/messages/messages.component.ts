import {Component, inject, OnInit} from '@angular/core';
import {first} from "rxjs/operators";
import {ContactMessage} from "../../models/contact-message";
import {ContactService} from "../../_services/contact.service";
import {Pagination} from "../../models/pagination";
import {NgClass} from "@angular/common";
import {NgxPaginationModule} from "ngx-pagination";
import {TruncatePipe} from "../../_helpers/truncate.pipe";
import {RouterLink} from "@angular/router";

@Component({
  selector: 'app-messages',
  templateUrl: './messages.component.html',
  imports: [
    NgxPaginationModule,
    TruncatePipe,
    NgClass,
    RouterLink
],
  styleUrls: []
})
export class MessagesComponent implements OnInit {
  private messageService = inject(ContactService);

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
