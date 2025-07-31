import {inject, Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable} from 'rxjs';
import {environment} from '../../environments/environment';

interface TotpResponse {
  access_token: string;
}

@Injectable({ providedIn: 'root' })
export class TotpAuthService {
  private readonly httpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  verifyTotp(totp: string, token: string): Observable<TotpResponse> {
    return this.httpClient.post<TotpResponse>(
      `${this.apiUrl}/users/login`,
      { totp, access_token: token }
    );
  }
}
