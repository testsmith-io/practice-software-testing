import { AbstractControl, ValidationErrors } from '@angular/forms';

export class DateValidators {
  static isoDate(control: AbstractControl): ValidationErrors | null {
    const value = control.value;
    const isoRegex = /^\d{4}-\d{2}-\d{2}$/;

    if (!value || !isoRegex.test(value)) {
      return { invalidDate: true };
    }

    const date = new Date(value);
    const [year, month, day] = value.split('-').map(Number);
    if (
      date.getFullYear() !== year ||
      date.getMonth() + 1 !== month ||
      date.getDate() !== day
    ) {
      return { invalidDate: true };
    }

    return null;
  }
}
