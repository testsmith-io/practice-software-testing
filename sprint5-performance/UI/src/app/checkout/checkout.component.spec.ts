import {ComponentFixture, TestBed} from '@angular/core/testing';

import {CheckoutComponent} from './checkout.component';
import {TranslocoTestingModule} from "@jsverse/transloco";
import en from "../../assets/i18n/en.json";

describe('CheckoutComponent', () => {
  let component: CheckoutComponent;
  let fixture: ComponentFixture<CheckoutComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [CheckoutComponent,TranslocoTestingModule.forRoot({
        langs: {en},
        translocoConfig: {
          availableLangs: ['en'],
          defaultLang: 'en',
        },
      })]
    })
      .compileComponents();

    fixture = TestBed.createComponent(CheckoutComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
