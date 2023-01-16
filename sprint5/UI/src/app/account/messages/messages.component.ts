import {Component, OnInit} from '@angular/core';
import {first} from "rxjs/operators";
import {ContactMessage} from "../../models/contact-message";
import {ContactService} from "../../_services/contact.service";
import {Pagination} from "../../models/pagination";

@Component({
  selector: 'app-messages',
  templateUrl: './messages.component.html',
  styleUrls: ['./messages.component.css']
})
export class MessagesComponent implements OnInit {

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
