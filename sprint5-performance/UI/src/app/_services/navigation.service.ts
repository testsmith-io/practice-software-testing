import {Injectable} from '@angular/core';

@Injectable({ providedIn: 'root' })
export class NavigationService {
  private readonly tokenKey = 'TOKEN_KEY';
  private readonly loginUrl = '/#/auth/login';

  redirectToLogin(): void {
    localStorage.removeItem(this.tokenKey);
    location.href = this.loginUrl;
  }
}
