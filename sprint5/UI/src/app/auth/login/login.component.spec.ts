import { ComponentFixture, TestBed } from '@angular/core/testing';
import { LoginComponent } from './login.component';
import { ReactiveFormsModule, FormsModule } from '@angular/forms';
import { CustomerAccountService } from '../../shared/customer-account.service';
import { TokenStorageService } from '../../_services/token-storage.service';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { RouterTestingModule } from '@angular/router/testing';
import {of, throwError} from 'rxjs';
import {Token} from "../../models/token.model";

describe('LoginComponent', () => {
  let component: LoginComponent;
  let fixture: ComponentFixture<LoginComponent>;
  let accountService: CustomerAccountService;
  let tokenStorage: TokenStorageService;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ LoginComponent ],
      imports: [ ReactiveFormsModule, FormsModule, HttpClientTestingModule, RouterTestingModule ],
      providers: [ CustomerAccountService, TokenStorageService ]
    })
      .compileComponents();

    fixture = TestBed.createComponent(LoginComponent);
    component = fixture.componentInstance;
    accountService = TestBed.inject(CustomerAccountService);
    tokenStorage = TestBed.inject(TokenStorageService);
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(true).toBeTruthy();
  });

  it('form should be invalid when empty', () => {
    expect(component.form.valid).toBeFalsy();
  });

  it('email field validity', () => {
    let email = component.form.controls['email'];
    expect(email.valid).toBeFalsy();
  });

  it('email field validity', () => {
    let errors: any = {};
    let email = component.form.controls['email'];

    // Required field
    errors = email.errors || {};
    expect(errors['required']).toBeTruthy();

    // Set email to something incorrect
    email.setValue("test");
    errors = email.errors || {};
    expect(errors['pattern']).toBeTruthy();

    // Set email to something correct
    email.setValue("test@example.com");
    errors = email.errors || {};
    expect(Object.keys(errors).length).toBeFalsy();
  });

  it('should call accountService.login on valid form submission', () => {
    const mockToken: Token = {
      access_token: 'token',
      token_type: 'Bearer',
      expires_in: 300
    };
    spyOn(accountService, 'getRole').and.returnValue('user');
    spyOn(accountService, 'redirectToAccount').and.stub();
    spyOn(accountService, 'login').and.returnValue(of(mockToken));
    component.form.controls['email'].setValue('test@example.com');
    component.form.controls['password'].setValue('password');
    component.onSubmit();
    expect(accountService.login).toHaveBeenCalled();
  });

  it('should handle invalid login', () => {
    const mockError = { error: 'Unauthorized' };
    spyOn(accountService, 'login').and.returnValue(throwError(() => mockError));

    component.form.controls['email'].setValue('invalid@example.com');
    component.form.controls['password'].setValue('invalid');
    component.onSubmit();

    expect(component.error).toBe('Invalid email or password');
    expect(component.isLoginFailed).toBeTrue();
    expect(accountService.login).toHaveBeenCalled();
  });

  it('should store token on successful login', () => {
    const mockToken: Token = {
      access_token: 'token',
      token_type: 'Bearer',
      expires_in: 300
    };

    spyOn(tokenStorage, 'saveToken');
    spyOn(accountService, 'login').and.returnValue(of(mockToken));
    spyOn(accountService, 'getRole').and.returnValue('user');
    spyOn(accountService, 'redirectToAccount').and.stub();
    component.form.controls['email'].setValue('test@example.com');
    component.form.controls['password'].setValue('password');
    component.onSubmit();
    expect(tokenStorage.saveToken).toHaveBeenCalledWith('token');
  });

});
