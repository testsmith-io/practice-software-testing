import {User} from "./user.model";

export class ContactMessageReply {
  message!: string;
  created_at?: string;
  user!: User;
}
