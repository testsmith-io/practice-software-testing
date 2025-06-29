import {Component, inject, OnInit} from '@angular/core';
import {ContactMessage} from "../../models/contact-message";
import {ContactService} from "../../_services/contact.service";
import {first} from "rxjs/operators";
import {Pagination} from "../../models/pagination";
import {NgClass} from "@angular/common";
import {RouterLink} from "@angular/router";
import {PaginationComponent} from "../../pagination/pagination.component";

@Component({
  selector: 'app-messages-list',
  templateUrl: './messages-list.component.html',
  imports: [
    NgClass,
    RouterLink,
    PaginationComponent
  ],
  styleUrls: []
})
export class MessagesListComponent implements OnInit {
  private readonly messageService = inject(ContactService);

  currentPage: number = 1;
  results: Pagination<ContactMessage>;

  ngOnInit(): void {
    this.getMessages();
  }

  getMessages() {
    this.messageService.getMessages(this.currentPage)
      .pipe(first())
      .subscribe((messages) => this.results = messages);
  }

  onPageChange(page: number) {
    // Handle page change here (e.g., fetch data for the selected page)
    this.currentPage = page;
    this.getMessages();
  }

}
