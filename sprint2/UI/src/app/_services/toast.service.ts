// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Injectable, TemplateRef} from '@angular/core';

export interface Toast {
  textOrTpl: string | TemplateRef<unknown>;
  [key: string]: unknown;
}

@Injectable({providedIn: 'root'})
export class ToastService {
  toasts: Toast[] = [];

  show(textOrTpl: string | TemplateRef<unknown>, options: Record<string, unknown> = {}) {
    this.toasts.push({textOrTpl, ...options});
  }

  remove(toast: Toast) {
    this.toasts = this.toasts.filter(t => t !== toast);
  }

  clear() {
    this.toasts.splice(0, this.toasts.length);
  }
}
