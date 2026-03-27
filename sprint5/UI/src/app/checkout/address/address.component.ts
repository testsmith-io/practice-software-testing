import {Component, EventEmitter, inject, Input, OnDestroy, OnInit, Output} from '@angular/core';
import {AbstractControl, FormBuilder, FormControl, FormGroup, ReactiveFormsModule, Validators} from "@angular/forms";
import {CustomerAccountService} from "../../shared/customer-account.service";
import {Subscription} from "rxjs";
import {TranslocoDirective} from "@jsverse/transloco";
import {NgClass} from "@angular/common";
import {ArchwizardModule} from "@y3krulez/angular-archwizard";

@Component({
  selector: 'app-address',
  templateUrl: './address.component.html',
  imports: [
    ReactiveFormsModule,
    TranslocoDirective,
    NgClass,
    ArchwizardModule
  ],
  styleUrls: []
})
export class AddressComponent implements OnInit, OnDestroy {
  private readonly formBuilder = inject(FormBuilder);
  private readonly customerAccountService = inject(CustomerAccountService);

  @Output() cusAddressChange = new EventEmitter<FormGroup>();
  @Input() address: FormGroup;
  cusAddress: FormGroup | any;
  private subscription: Subscription = new Subscription();

  ngOnInit(): void {
    this.initializeForm();
    this.setAddress();
  }

  ngOnDestroy(): void {
    this.subscription.unsubscribe();
  }

  private initializeForm(): void {
    this.cusAddress = this.formBuilder.group({
      address: new FormGroup({
        street: new FormControl('', [Validators.required, Validators.maxLength(70)]),
        city: new FormControl('', [Validators.required, Validators.maxLength(40)]),
        state: new FormControl('', [Validators.required, Validators.maxLength(40)]),
        country: new FormControl('', [Validators.required, Validators.maxLength(40)]),
        postal_code: new FormControl('', [Validators.required, Validators.maxLength(10)]),
      })
    });

    this.subscription.add(
      this.cusAddress.valueChanges.subscribe(() => {
        this.cusAddressChange.emit(this.cusAddress);
      })
    );
  }

  setAddress(): void {
    if (this.address) {
      this.cusAddress.patchValue(this.address.value);
    } else {
      this.subscription.add(
        this.customerAccountService.getDetails().subscribe(customer => {
          this.cusAddress.patchValue(customer);
        })
      );
    }
  }

  get f(): { [key: string]: AbstractControl } {
    return this.cusAddress.controls;
  }

  protected readonly console = console;
}
