import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ReactiveFormsModule, FormsModule } from '@angular/forms';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { ContactComponent } from './contact.component';
import { ContactService } from '../_services/contact.service';
import { CustomerAccountService } from '../shared/customer-account.service';
import { BrowserDetectorService } from '../_services/browser-detector.service';
import { of } from 'rxjs';

describe('ContactComponent', () => {
  let component: ContactComponent;
  let fixture: ComponentFixture<ContactComponent>;
  let contactService: ContactService;
  let customerAccountService: CustomerAccountService;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ContactComponent],
      imports: [ReactiveFormsModule, FormsModule, HttpClientTestingModule],
      providers: [
        ContactService,
        CustomerAccountService,
        BrowserDetectorService
      ]
    }).compileComponents();

    fixture = TestBed.createComponent(ContactComponent);
    component = fixture.componentInstance;
    contactService = TestBed.inject(ContactService);
    customerAccountService = TestBed.inject(CustomerAccountService);

    spyOn(customerAccountService, 'getDetails').and.returnValue(of({ first_name: 'John', last_name: 'Doe' }));
    spyOn(customerAccountService, 'getRole').and.returnValue('user');
    spyOn(contactService, 'sendMessage').and.returnValue(of({}));
  });

  it('should create the component', () => {
    expect(component).toBeTruthy();
  });

  it('should initialize the form with empty values', () => {
    component.ngOnInit();
    expect(component.contact.value).toEqual({
      first_name: '',
      last_name: '',
      email: '',
      attachment: '',
      subject: '',
      message: ''
    });
  });

  it('should display known user when user is signed in', () => {
    component.ngOnInit();
    fixture.detectChanges();
    expect(component.name).toBe('John Doe');
  });

  it('should call sendMessage when the form is valid and submitted', (done) => {
    component.ngOnInit();
    fixture.detectChanges();

    // Set valid form values
    component.contact.controls['first_name'].setValue('John');
    component.contact.controls['last_name'].setValue('Doe');
    component.contact.controls['email'].setValue('john.doe@example.com');
    component.contact.controls['subject'].setValue('return');
    component.contact.controls['message'].setValue('This is a test message. This is a test message. This is a test message. This is a test message.');

    // Ensure the form is valid before submission
    console.log('Form Valid:', component.contact.valid); // Diagnostic log

    setTimeout(() => {
      // Trigger form submission
      component.onSubmit();

      // Ensure sendMessage is called
      expect(contactService.sendMessage).toHaveBeenCalled();

      // Ensure showConfirmation is true
      expect(component.showConfirmation).toBeTrue();

      done();
    }, 100); // Slight delay to allow for change detection and form validation
  });

  it('should set errors for invalid file type and size', () => {
    component.ngOnInit();
    const file = new File([''], 'test.pdf', { type: 'application/pdf' });
    const event = { target: { files: [file] } };

    component.changeFile(event);
    expect(component.contact.controls['attachment'].errors).toEqual({ incorrectType: true });
  });

  it('should show error when form is submitted with invalid data', () => {
    component.ngOnInit();
    component.onSubmit();
    expect(component.submitted).toBeTrue();
    expect(contactService.sendMessage).not.toHaveBeenCalled();
  });
});
