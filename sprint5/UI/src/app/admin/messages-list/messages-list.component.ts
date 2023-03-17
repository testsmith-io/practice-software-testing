import {Component, OnInit} from '@angular/core';
import {ContactMessage} from "../../models/contact-message";
import {ContactService} from "../../_services/contact.service";
import {first} from "rxjs/operators";
import {Pagination} from "../../models/pagination";

@Component({
  selector: 'app-messages-list',
  templateUrl: './messages-list.component.html',
  styleUrls: ['./messages-list.component.css']
})
export class MessagesListComponent implements OnInit {

  p: number = 1;
  results: Pagination<ContactMessage>;

  constructor(private messageService: ContactService) {
  }

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
