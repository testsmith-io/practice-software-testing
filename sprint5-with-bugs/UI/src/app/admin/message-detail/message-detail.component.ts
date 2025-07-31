import {Component, inject, OnInit} from '@angular/core';
import {ContactMessage} from "../../models/contact-message";
import {ContactService} from "../../_services/contact.service";
import {first} from "rxjs/operators";
import {ActivatedRoute, RouterLink} from "@angular/router";
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from "@angular/forms";
import {MessageState} from "../../models/message-state";
import {NgClass} from "@angular/common";

@Component({
  selector: 'app-message-detail',
  templateUrl: './message-detail.component.html',
  imports: [
    NgClass,
    RouterLink,
    ReactiveFormsModule
  ],
  styleUrls: []
})
export class MessageDetailComponent implements OnInit {
  private readonly messageService = inject(ContactService);
  private readonly route = inject(ActivatedRoute);
  private readonly formBuilder = inject(FormBuilder);

  statuses = ["NEW", "IN_PROGRESS", "RESOLVED"];
  message!: ContactMessage;
  form: FormGroup;
  statusForm: FormGroup;
  submitted: boolean = false;
  isUpdated: boolean = false;
  hideAlert: boolean = false;
  error: string;
  messageState: any = MessageState;
  id: string;

  ngOnInit(): void {
    this.statusForm = this.formBuilder.group(
      {
        status: ['', [Validators.required]]
      });

    this.form = this.formBuilder.group({
      message: ['', []]
    });

    this.id = this.route.snapshot.params["id"];
    this.getMessage();
  }

  getMessage() {
    this.messageService.getMessage(this.id)
      .pipe(first())
      .subscribe((message) => {
        this.message = message;
        this.statusForm.controls['status'].setValue(message.status);
      });
  }

  statusUpdate() {
    let status = this.statusForm.controls['status'].value;
    if (this.message.status == this.statusForm.controls['status'].value) {
      this.error = 'No new status selected.';
      return;
    }
    this.messageService.updateStatus(this.message.id, status).pipe(first())
      .subscribe({
        next: () => {
          this.message.status = status;
        }
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
      message: this.form.value.message
    };

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

}
