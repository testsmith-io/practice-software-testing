import {Injectable} from '@angular/core';
import {ActivatedRouteSnapshot, CanActivate, Router} from '@angular/router';
import {CustomerAccountService} from "./shared/customer-account.service";
import {Observable, of,map} from "rxjs";
import {catchError} from "rxjs/operators";

@Injectable()
export class UserAuthGuard implements CanActivate {
  constructor(private auth: CustomerAccountService, private router: Router) {
  }

  canActivate(route: ActivatedRouteSnapshot): Observable<boolean> {
    return this.auth.isAuthenticated()
      .pipe(
        map(isAuth => {
          if (!isAuth || this.auth.getRole() !== 'user' ) {
            this.router.navigate(['auth/login']);
            return false;
          } else {
            return true;
          }
        }),
        catchError((error, caught) => {
          return of(false);
        })
      );
  }

  // canActivate() {
  //   if (this.auth.isAuthenticated() && this.auth.getRole() === 'user') {
  //     return true;
  //   } else {
  //     this.router.navigate(['auth/login']);
  //     return false;
  //   }
  // }
}
