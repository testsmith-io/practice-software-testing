import {Injectable} from '@angular/core';

@Injectable({ providedIn: 'root' })
export class BrowserService {
  open(url: string, target: string = '', features: string = ''): void {
    window.open(url, target, features);
  }
}
