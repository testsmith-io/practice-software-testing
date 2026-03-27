import {User} from "./user.model";

export interface ContactMessageReply {
  message?: string;
  created_at?: string;
  user?: User;
}
