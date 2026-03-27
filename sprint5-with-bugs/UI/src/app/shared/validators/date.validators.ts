import { AbstractControl, ValidationErrors } from '@angular/forms';

export class DateValidators {
  static isoDate(control: AbstractControl): ValidationErrors | null {
    const value = control.value;
    const isoRegex = /^\d{4}-\d{2}-\d{2}$/;

    if (typeof value !== 'string' || !isoRegex.test(value)) {
      return { invalidDate: true };
    }

    const [year, month, day] = value.split('-').map(Number);

    // Create date using UTC to avoid timezone shifts
    const utc = new Date(Date.UTC(year, month - 1, day));

    if (
      utc.getUTCFullYear() !== year ||
      utc.getUTCMonth() + 1 !== month ||
      utc.getUTCDate() !== day
    ) {
      return { invalidDate: true };
    }

    return null;
  }
}
