import {Component, OnInit} from '@angular/core';
import {ContactMessage} from "../../../models/contact-message";
import {FormBuilder, FormGroup} from "@angular/forms";
import {ContactService} from "../../../_services/contact.service";
import {ActivatedRoute} from "@angular/router";
import {first} from "rxjs/operators";
import {Title} from "@angular/platform-browser";

@Component({
  selector: 'app-message-detail',
  templateUrl: './message-detail.component.html',
  styleUrls: ['./message-detail.component.css']
})
export class MessageDetailComponent implements OnInit {
  statuses = ["NEW", "IN_PROGRESS", "RESOLVED"];
  message!: ContactMessage;
  form: FormGroup;
  submitted: boolean = false;
  isUpdated: boolean = false;
  hideAlert: boolean = false;
  error: string;

  id: string;

  constructor(private messageService: ContactService,
              private route: ActivatedRoute,
              private formBuilder: FormBuilder,
              private titleService: Title) { }

  ngOnInit(): void {
    this.form = this.formBuilder.group({
      message: ['', []]
    });

    this.id = this.route.snapshot.params["id"];
    this.getMessage();
  }

  getMessage() {
    this.messageService.getMessage(this.id)
      .pipe(first())
      .subscribe((message)=> {
        this.message = message;
        this.updateTitle(this.message.id.toString());
      });
  }

  get f() {
    return this.form.controls;
  }

  onSubmit() {
    this.submitted = true;
    this.isUpdated = false;

    if (this.form.invalid) {
      return;
    }

    this.addReply();
  }

  private addReply() {
    let messageId = this.message.id;

    const payload: ContactMessage = {
      message: this.form.value.message};

    this.messageService.addReply(payload, String(messageId))
      .pipe(first())
      .subscribe({
        next: () => {
          this.isUpdated = true;
          this.getMessage();
          this.reset();
        }, error: (err) => {
          this.error = Object.values(err).join('\r\n');
        }, complete: () => {
          this.hideAlert = false;
        }
      });
  }

  private reset() {
    for (let name in this.form.controls) {
      this.form.controls[name].setValue('');
      this.form.controls[name].setErrors(null);
    }
  }

  private updateTitle(messageId: string) {
    this.titleService.setTitle(`Message: ${messageId} - Practice Software Testing - Toolshop - v5.0`);
  }

}
