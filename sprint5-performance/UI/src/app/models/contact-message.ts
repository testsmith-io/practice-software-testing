import {User} from "./user.model";
import {ContactMessageReply} from "./contact-message-reply";

export class ContactMessage {
  id?: number;
  name?: string;
  email?: string;
  subject?: string;
  message!: string;
  created_at?: string;
  status?: string;
  user?: User;
  replies?: ContactMessageReply[];
}
