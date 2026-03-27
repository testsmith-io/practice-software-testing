import {inject, Injectable} from '@angular/core';
import {ActivatedRouteSnapshot, CanActivate, Router} from '@angular/router';
import {CustomerAccountService} from "./shared/customer-account.service";
import {map, Observable, of} from "rxjs";
import {catchError} from "rxjs/operators";

@Injectable()
export class UserAuthGuard implements CanActivate {
  private readonly auth = inject(CustomerAccountService);
  private readonly router = inject(Router);

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
        catchError(() => {
          return of(false);
        })
      );
  }

}
