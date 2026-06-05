// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {User} from "./user.model";
import {ContactMessageReply} from "./contact-message-reply";

export interface ContactMessage {
  id?: number;
  name?: string;
  email?: string;
  subject?: string;
  message?: string;
  created_at?: string;
  status?: string;
  user?: User;
  replies?: ContactMessageReply[];
}
