// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Pipe, PipeTransform} from '@angular/core';

@Pipe({name: 'replaceUnderscores'})
export class ReplaceUnderscoresPipe implements PipeTransform {
  transform(value: any): string {
    return value.replace(/_/g, ' ');
  }
}
