// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Injectable} from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class RedirectService {
  redirectTo(url: string): void {
    window.location.href = url;
  }
}
