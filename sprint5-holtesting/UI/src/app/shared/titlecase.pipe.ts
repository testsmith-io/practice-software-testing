// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Pipe, PipeTransform} from '@angular/core';

@Pipe({name: 'titleCase'})
export class TitleCasePipe implements PipeTransform {
  transform(value: any): string {
    if (!value) return '';
    return value
      .toLowerCase()
      .split(' ')
      .map((word: string) => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
  }
}
