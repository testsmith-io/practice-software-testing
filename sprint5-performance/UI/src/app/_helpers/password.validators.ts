import {AbstractControl, FormGroup, ValidationErrors, ValidatorFn} from '@angular/forms';

export class PasswordValidators {
  static minLength(min: number): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      const isValid = (control.value || '').length >= min;
      return isValid ? null : { minLength: { requiredLength: min } };
    };
  }

  static mixedCase(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      const hasUpperCase = /[A-Z]/.test(control.value || '');
      const hasLowerCase = /[a-z]/.test(control.value || '');
      return hasUpperCase && hasLowerCase ? null : { mixedCase: true };
    };
  }

  static hasNumber(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      const hasNumber = /\d/.test(control.value || '');
      return hasNumber ? null : { hasNumber: true };
    };
  }

  static hasSymbol(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      const hasSymbol = /[!"#$%&'()*+,-./:;<=>?@[\\\]^_`{|}~]/.test(control.value || '');
      return hasSymbol ? null : { hasSymbol: true };
    };
  }

  static passwordsMatch(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      const formGroup = control as FormGroup;
      const newPassword = formGroup.get('new_password')?.value;
      const confirmPassword = formGroup.get('new_password_confirmation')?.value;
      return newPassword === confirmPassword ? null : { passwordsMismatch: true };
    };
  }
}
