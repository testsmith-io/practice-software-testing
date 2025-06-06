import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';

@Injectable({ providedIn: 'root' })
export class TotpAuthService {
  constructor(private http: HttpClient) {}

  verifyTotp(totp: string, token: string) {
    return this.http.post<{ access_token: string }>(
      `${environment.apiUrl}/users/login`,
      { totp, access_token: token }
    );
  }
}
