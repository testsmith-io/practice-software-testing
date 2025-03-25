import { Injectable } from '@angular/core';

@Injectable({ providedIn: 'root' })
export class NavigationService {
  redirectToLogin() {
    window.localStorage.removeItem('TOKEN_KEY');
    window.location.href = '/#/auth/login';
  }
}
