import {Component, EventEmitter, Input, OnDestroy, OnInit, Output} from '@angular/core';
import {AbstractControl, FormBuilder, FormGroup, Validators} from "@angular/forms";
import {CustomerAccountService} from "../../shared/customer-account.service";
import {Subscription} from "rxjs";

@Component({
  selector: 'app-address',
  templateUrl: './address.component.html',
  styleUrls: ['./address.component.css'] // Corrected 'styleUrl' to 'styleUrls'
})
export class AddressComponent implements OnInit, OnDestroy {

  @Output() cusAddressChange = new EventEmitter<FormGroup>();
  @Input() address: FormGroup;
  cusAddress: FormGroup | any;
  private subscription: Subscription = new Subscription();

  constructor(
    private formBuilder: FormBuilder,
    private customerAccountService: CustomerAccountService
  ) {
  }

  ngOnInit(): void {
    this.initializeForm();
    this.setAddress();
  }

  ngOnDestroy(): void {
    this.subscription.unsubscribe();
  }

  private initializeForm(): void {
    this.cusAddress = this.formBuilder.group({
      address: ['', [Validators.required, Validators.maxLength(70)]],
      city: ['', [Validators.required, Validators.maxLength(40)]],
      state: ['', [Validators.required, Validators.maxLength(40)]],
      country: ['', [Validators.required, Validators.maxLength(40)]],
      postcode: ['', [Validators.required, Validators.maxLength(10)]]
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
