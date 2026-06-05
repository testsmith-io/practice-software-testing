// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Address} from "./address";

export interface User {
  id?: number;
  first_name?: string;
  last_name?: string;
  dob?: string;
  address?: Address;
  phone?: boolean;
  email: boolean;
  password?: boolean;
}
