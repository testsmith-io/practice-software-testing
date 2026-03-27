import {ComponentFixture, fakeAsync, TestBed, tick} from '@angular/core/testing';
import {InvoicesComponent} from './invoices.component';
import {InvoiceService} from '../../_services/invoice.service';
import {of, throwError} from 'rxjs';
import {CUSTOM_ELEMENTS_SCHEMA} from '@angular/core';
import {By} from '@angular/platform-browser';
import {Pagination} from "../../models/pagination";
import {Invoice} from "../../models/invoice";
import {NavigationService} from "../../_services/navigation.service";
import {TranslocoTestingModule} from '@jsverse/transloco';
import en from '../../../assets/i18n/en.json';
import {RouterTestingModule} from "@angular/router/testing";

// Mock data
const mockInvoices: Pagination<Invoice> = {
  "current_page": 1, "data": [{
    "id": "01JQ77CQ73W932YPJ6567CG7MK",
    "invoice_date": "2025-03-19 18:00:15",
    "additional_discount_percentage": null,
    "additional_discount_amount": null,
    "invoice_number": "INV-20250000011",
    "billing_street": "Test street 12",
    "billing_city": "Utrecht",
    "billing_state": "Utrecht",
    "billing_country": "The Netherlands",
    "billing_postal_code": "1122AB",
    "subtotal": null,
    "total": 855.58,
    "status": "COMPLETED",
    "status_message": null,
    "created_at": "2025-03-25T18:00:18.000000Z",
    "user_id": "01JQ77CMHCWQCC6RKR7YB4MJHS",
    "invoicelines": [{
      "id": "01JQ77D114ZS82CRFZCYAT8W8X",
      "unit_price": 22.96,
      "quantity": 2,
      "discount_percentage": null,
      "discounted_price": null,
      "invoice_id": "01JQ77CQ73W932YPJ6567CG7MK",
      "product_id": "01JQ77CMM7XFEDCKTB76SC3DGY",
      "product": {
        "id": "01JQ77CMM7XFEDCKTB76SC3DGY",
        "name": "Swiss Woodcarving Chisels",
        "description": "Aliquam hendrerit interdum quam sed aliquam. Donec finibus ligula nec nisi bibendum, et bibendum tortor pharetra. Pellentesque eu dui suscipit, maximus risus vitae, rutrum orci. Sed dolor quam, elementum eu erat vel, venenatis eleifend tortor. Sed posuere posuere efficitur. Nam sit amet tortor id metus bibendum lobortis et sit amet odio. Aenean ultricies sapien eget risus lacinia porttitor. Vivamus lorem elit, consequat in sem non, dapibus lacinia justo. Aliquam erat volutpat.",
        "price": 22.96,
        "is_location_offer": false,
        "is_rental": false,
        "in_stock": true
      }
    }, {
      "id": "01JQ77D11MM5QSAM3C0RFG97FH",
      "unit_price": 178.2,
      "quantity": 3,
      "discount_percentage": null,
      "discounted_price": null,
      "invoice_id": "01JQ77CQ73W932YPJ6567CG7MK",
      "product_id": "01JQ77CMN0QHJESVJRQRSHN4FB",
      "product": {
        "id": "01JQ77CMN0QHJESVJRQRSHN4FB",
        "name": "Workbench with Drawers",
        "description": "Nulla vitae lacus risus. Phasellus accumsan mi nec consectetur euismod. Donec a finibus quam. Proin id tempus tellus. Etiam vel lectus vel nunc convallis commodo. In massa ante, hendrerit eu ultricies ac, sagittis quis urna. Aliquam porttitor varius mattis. Etiam ac auctor risus, vitae pellentesque enim. Donec convallis est vel tellus dictum rhoncus. Vestibulum suscipit mollis diam, vehicula facilisis libero egestas sed. Mauris at urna aliquet, iaculis dui et, aliquam nunc.",
        "price": 178.2,
        "is_location_offer": false,
        "is_rental": false,
        "in_stock": true
      }
    }, {
      "id": "01JQ77D122017RHENM7MMVZG4S",
      "unit_price": 10.07,
      "quantity": 3,
      "discount_percentage": null,
      "discounted_price": null,
      "invoice_id": "01JQ77CQ73W932YPJ6567CG7MK",
      "product_id": "01JQ77CMMAEKA3MWJRC9HNV69Y",
      "product": {
        "id": "01JQ77CMMAEKA3MWJRC9HNV69Y",
        "name": "Measuring Tape",
        "description": "Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nunc eu orci orci. Mauris maximus ligula sed eros faucibus maximus. Aliquam vitae iaculis mi. In sit amet nulla gravida, egestas risus ut, condimentum dolor. Phasellus vulputate, ligula at eleifend congue, mauris justo lobortis justo, id mattis massa orci in quam. Fusce sit amet enim id tellus facilisis fermentum vitae eget velit. Quisque accumsan, libero ut ullamcorper aliquet, orci mauris imperdiet quam, quis ultricies elit elit quis arcu. Integer scelerisque nulla vel magna porttitor commodo.",
        "price": 10.07,
        "is_location_offer": false,
        "is_rental": false,
        "in_stock": true
      }
    }, {
      "id": "01JQ77D12CH3BE22NW025XZXB0",
      "unit_price": 45.23,
      "quantity": 1,
      "discount_percentage": null,
      "discounted_price": null,
      "invoice_id": "01JQ77CQ73W932YPJ6567CG7MK",
      "product_id": "01JQ77CMM6EJ3XPH52V3VP689M",
      "product": {
        "id": "01JQ77CMM6EJ3XPH52V3VP689M",
        "name": "Wood Carving Chisels",
        "description": "Fusce cursus cursus leo, nec pharetra turpis mattis porttitor. Ut fringilla, nisl a iaculis dictum, enim ante eleifend nisl, fermentum fringilla ligula nunc quis eros. Donec in magna quis ex placerat dapibus. Vivamus in iaculis elit. Nulla commodo tincidunt urna, sed gravida erat egestas in. Nunc mauris sapien, placerat vitae vehicula quis, sagittis a est. Morbi condimentum vitae enim in hendrerit. Suspendisse est magna, ullamcorper et porta id, auctor eu est. Nulla id magna vestibulum, porttitor tellus porta, maximus libero. Integer pulvinar mauris at posuere interdum. Nam vel ligula placerat, condimentum lacus quis, gravida eros. Curabitur sit amet erat ipsum. Sed ut suscipit sem, ac lacinia purus. Nam dignissim imperdiet dolor, quis efficitur nulla tincidunt non.",
        "price": 45.23,
        "is_location_offer": true,
        "is_rental": false,
        "in_stock": true
      }
    }, {
      "id": "01JQ77D12QKAT5SRNMTVJEYTY1",
      "unit_price": 66.54,
      "quantity": 3,
      "discount_percentage": null,
      "discounted_price": null,
      "invoice_id": "01JQ77CQ73W932YPJ6567CG7MK",
      "product_id": "01JQ77CMNDNXAK4BAXCJJG65TW",
      "product": {
        "id": "01JQ77CMNDNXAK4BAXCJJG65TW",
        "name": "Cordless Drill 24V",
        "description": "Aenean vel dolor eu erat rutrum dapibus. Aenean consectetur velit in quam pulvinar volutpat. Etiam at laoreet augue. Sed sed diam venenatis, pharetra quam a, consectetur massa. Vivamus purus enim, placerat non augue eu, viverra sagittis purus. Sed dictum massa ac orci posuere, pulvinar dignissim est mattis. Curabitur at convallis ipsum. Donec id arcu vel massa tincidunt porta. Nullam quis accumsan mauris.",
        "price": 66.54,
        "is_location_offer": false,
        "is_rental": false,
        "in_stock": true
      }
    }],
    "payment": {"payment_method": "buy-now-pay-later", "payment_details": {"monthly_installments": "3"}}
  }], "from": 1, "last_page": 11, "per_page": 15, "to": 15, "total": 160
};

describe('InvoicesComponent', () => {
  let component: InvoicesComponent;
  let fixture: ComponentFixture<InvoicesComponent>;
  let invoiceServiceSpy: jasmine.SpyObj<InvoiceService>;
  let navigationServiceSpy: jasmine.SpyObj<NavigationService>;

  beforeEach(async () => {
    const invoiceSpy = jasmine.createSpyObj('InvoiceService', ['getInvoices']);
    const navSpy = jasmine.createSpyObj('NavigationService', ['redirectToLogin']);

    await TestBed.configureTestingModule({
      imports: [
        RouterTestingModule,
        InvoicesComponent,
        TranslocoTestingModule.forRoot({
          langs: {en},
          translocoConfig: {
            availableLangs: ['en'],
            defaultLang: 'en',
          },
        })
      ],
      providers: [
        {provide: InvoiceService, useValue: invoiceSpy},
        {provide: NavigationService, useValue: navSpy}
      ],
      schemas: [CUSTOM_ELEMENTS_SCHEMA]
    }).compileComponents();

    invoiceServiceSpy = TestBed.inject(InvoiceService) as jasmine.SpyObj<InvoiceService>;
    navigationServiceSpy = TestBed.inject(NavigationService) as jasmine.SpyObj<NavigationService>;

  });

  beforeEach(() => {
    invoiceServiceSpy.getInvoices.and.returnValue(of(mockInvoices));
    fixture = TestBed.createComponent(InvoicesComponent);
    component = fixture.componentInstance;
  });

  it('should create', () => {
    invoiceServiceSpy.getInvoices.and.returnValue(of(mockInvoices));
    fixture.detectChanges(); // triggers ngOnInit
    expect(component).toBeTruthy();
  });

  it('should call getInvoices on init and set results', fakeAsync(() => {
    invoiceServiceSpy.getInvoices.and.returnValue(of(mockInvoices));
    fixture.detectChanges();
    tick();
    expect(invoiceServiceSpy.getInvoices).toHaveBeenCalledWith(1);
    expect(component.results).toEqual(mockInvoices);
  }));

  it('should handle unauthorized error and redirect to login', fakeAsync(() => {
    invoiceServiceSpy.getInvoices.and.returnValue(throwError({status: 401}));
    fixture.detectChanges();
    tick();
    expect(navigationServiceSpy.redirectToLogin).toHaveBeenCalled(); // ✅ Use service spy
  }));

  it('should render invoice data in the template', async () => {
    // Arrange
    invoiceServiceSpy.getInvoices.and.returnValue(of(mockInvoices));

    // Act
    fixture.detectChanges();
    await fixture.whenStable();
    await fixture.whenRenderingDone();
    fixture.detectChanges();

    // Debug output (optional)
    const html = fixture.nativeElement.innerHTML;
    console.log('Rendered HTML:', html);

    // Assert
    const rows = fixture.debugElement.queryAll(By.css('tbody tr'));
    expect(rows.length).withContext('No <tr> rows rendered').toBeGreaterThan(0);

    const rowText = rows[0]?.nativeElement?.textContent || '';
    expect(rowText).toContain('INV-20250000011');
    expect(rowText).toContain('Test street 12');
    expect(rowText).toContain('2025-03-19');
    expect(rowText).toContain('855.58');
  });



  it('should handle page change and fetch invoices again', fakeAsync(() => {
    invoiceServiceSpy.getInvoices.and.returnValue(of(mockInvoices));
    fixture.detectChanges();
    tick();

    component.onPageChange(2);
    tick();

    expect(component.currentPage).toBe(2);
    expect(invoiceServiceSpy.getInvoices).toHaveBeenCalledWith(2);
  }));
});
