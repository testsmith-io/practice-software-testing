// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {User} from "./user.model";

export interface ContactMessageReply {
  message?: string;
  created_at?: string;
  user?: User;
}
