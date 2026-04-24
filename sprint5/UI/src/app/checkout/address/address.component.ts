// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {Component, EventEmitter, inject, Input, OnDestroy, OnInit, Output} from '@angular/core';
import {AbstractControl, FormBuilder, FormControl, FormGroup, ReactiveFormsModule, Validators} from "@angular/forms";
import {CustomerAccountService} from "../../shared/customer-account.service";
import {Subscription} from "rxjs";
import {debounceTime, distinctUntilChanged} from 'rxjs/operators';
import {TranslocoDirective} from "@jsverse/transloco";
import {NgClass} from "@angular/common";
import {ArchwizardModule} from "@y3krulez/angular-archwizard";
import {PostcodeService} from "../../_services/postcode.service";
import countriesList from '../../../assets/countries.json';

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
  private readonly postcodeService = inject(PostcodeService);

  @Output() cusAddressChange = new EventEmitter<FormGroup>();
  @Input() address: FormGroup;
  cusAddress: FormGroup | any;
  postcodeLookupPending = false;
  countries = countriesList;
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
        house_number: new FormControl('', [Validators.required, Validators.maxLength(10)]),
      })
    });

    this.subscription.add(
      this.cusAddress.valueChanges.subscribe(() => {
        this.cusAddressChange.emit(this.cusAddress);
      })
    );

    const addressGroup = this.cusAddress.get('address') as FormGroup;
    this.subscription.add(
      addressGroup.get('postal_code').valueChanges
        .pipe(debounceTime(300), distinctUntilChanged())
        .subscribe(() => this.tryPostcodeLookup())
    );
    this.subscription.add(
      addressGroup.get('house_number').valueChanges
        .pipe(debounceTime(300), distinctUntilChanged())
        .subscribe(() => this.tryPostcodeLookup())
    );
    this.subscription.add(
      addressGroup.get('country').valueChanges
        .pipe(distinctUntilChanged())
        .subscribe(() => this.tryPostcodeLookup())
    );
  }

  private tryPostcodeLookup(): void {
    const addressGroup = this.cusAddress.get('address') as FormGroup;
    const country = addressGroup.get('country').value;
    const postcode = addressGroup.get('postal_code').value;
    const houseNumber = addressGroup.get('house_number').value;
    if (!country || !postcode || !houseNumber) {
      return;
    }

    this.postcodeLookupPending = true;
    this.subscription.add(
      this.postcodeService.lookup(country, postcode, houseNumber).subscribe({
        next: (result) => {
          this.postcodeLookupPending = false;
          addressGroup.patchValue({
            street: result.street,
            city: result.city,
            state: result.state,
          });
        },
        error: () => {
          this.postcodeLookupPending = false;
        },
      })
    );
  }

  setAddress(): void {
    if (this.address) {
      this.cusAddress.patchValue(this.address.value);
    } else {
      // Check if guest checkout info exists
      const guestInfo = sessionStorage.getItem('guestCheckout');
      if (guestInfo) {
        // For guest users, they need to fill in the address manually
        return;
      }

      // For logged-in users, fetch their saved address
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
