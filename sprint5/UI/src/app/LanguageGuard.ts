import {ActivatedRouteSnapshot, CanActivate, Router, RouterStateSnapshot} from "@angular/router";
import {inject, Injectable} from "@angular/core";

@Injectable({ providedIn: 'root' })
export class LanguageGuard implements CanActivate {
  private readonly router = inject(Router);

  defaultLang = 'en';
  supportedLangs = ['en', 'fr', 'de', 'es'];

  canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): boolean {
    const lang = route.params['lang'];
    if (lang && !this.supportedLangs.includes(lang)) {
      this.router.navigate([this.defaultLang]);
      return false;
    }

    if (lang === this.defaultLang) {
      this.router.navigate([state.url.substring(4)]);
      return false;
    }
    return true;
  }
}
