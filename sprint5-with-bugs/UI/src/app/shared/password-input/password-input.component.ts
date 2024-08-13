import {Component, forwardRef, Input} from '@angular/core';
import { ControlValueAccessor, NG_VALUE_ACCESSOR } from '@angular/forms';

@Component({
  selector: 'app-password-input',
  templateUrl: './password-input.component.html',
  styleUrls: ['./password-input.component.css'],
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => PasswordInputComponent),
      multi: true
    }
  ]
})
export class PasswordInputComponent implements ControlValueAccessor {
  @Input() id: string = 'password';
  @Input() placeholder: string = '';
  @Input() isInvalid: boolean = false;
  @Input() ariaDescribedBy: string | null = null;
  @Input() ariaInvalid: boolean = false;

  passwordFieldType: string = 'password';
  value: string = '';  // This will hold the value of the input field
  disabled: boolean = false;

  // Callbacks to be registered
  onChange: (value: string) => void = () => {};
  onTouched: () => void = () => {};

  // This method will be called by Angular to set the value
  writeValue(value: string): void {
    this.value = value;
  }

  // This method will be called by Angular to register the change callback
  registerOnChange(fn: (value: string) => void): void {
    this.onChange = fn;
  }

  // This method will be called by Angular to register the touched callback
  registerOnTouched(fn: () => void): void {
    this.onTouched = fn;
  }

  // This method will be called by Angular to disable/enable the component
  setDisabledState?(isDisabled: boolean): void {
    this.disabled = isDisabled;
  }

  togglePasswordVisibility(): void {
    this.passwordFieldType = this.passwordFieldType === 'password' ? 'text' : 'password';
  }

  // This method is called when the user makes changes in the input field
  onInputChange(event: Event): void {
    const inputElement = event.target as HTMLInputElement;
    this.value = inputElement.value;
    this.onChange(this.value);  // Notify Angular of the change
  }

  // This method is called when the input field is touched
  onInputBlur(): void {
    this.onTouched();  // Notify Angular that the input was touched
  }
}
