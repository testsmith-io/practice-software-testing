// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

export interface Token {
  access_token: string;
  token_type: string;
  expires_in: number;
  requires_totp?: any;
}
