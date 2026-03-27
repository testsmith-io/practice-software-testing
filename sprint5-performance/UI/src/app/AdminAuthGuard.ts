import {Injectable} from '@angular/core';
import {ActivatedRouteSnapshot, CanActivate, Router} from '@angular/router';
import {CustomerAccountService} from "./shared/customer-account.service";
import {map, Observable, of} from "rxjs";
import {catchError} from "rxjs/operators";

@Injectable()
export class AdminAuthGuard implements CanActivate {
  constructor(private auth: CustomerAccountService,
              private router: Router) {
  }

  canActivate(route: ActivatedRouteSnapshot): Observable<boolean> {
    return this.auth.isAuthenticated()
      .pipe(
        map(isAuth => {
          if (!isAuth || this.auth.getRole() !== 'admin') {
            this.router.navigate(['auth/login']);
            return false;
          } else {
            return true;
          }
        }),
        catchError(() => {
          return of(false);
        })
      );
  }
}
