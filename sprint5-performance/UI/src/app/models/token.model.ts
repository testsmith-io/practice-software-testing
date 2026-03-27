export interface Token {
  access_token: string;
  token_type: string;
  expires_in: number;
  requires_totp?: any;
}
