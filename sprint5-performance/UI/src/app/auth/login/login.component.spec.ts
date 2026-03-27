import {ComponentFixture, TestBed} from '@angular/core/testing';
import {FormBuilder, ReactiveFormsModule} from '@angular/forms';
import {ActivatedRoute} from '@angular/router';
import {of, Subject, throwError} from 'rxjs';

import {LoginComponent} from './login.component';
import {CustomerAccountService} from '../../shared/customer-account.service';
import {TokenStorageService} from '../../_services/token-storage.service';
import {TotpAuthService} from '../../_services/totp-auth.service';
import {BrowserService} from '../../_services/browser.service';
import {TranslocoTestingModule} from "@jsverse/transloco";
import en from "../../../assets/i18n/en.json";

describe('LoginComponent', () => {
  let component: LoginComponent;
  let fixture: ComponentFixture<LoginComponent>;
  let mockCustomerAccountService: jasmine.SpyObj<CustomerAccountService>;
  let mockTokenStorageService: jasmine.SpyObj<TokenStorageService>;
  let mockTotpAuthService: jasmine.SpyObj<TotpAuthService>;
  let mockBrowserService: jasmine.SpyObj<BrowserService>;
  let mockActivatedRoute: any;

  beforeEach(async () => {
    mockCustomerAccountService = jasmine.createSpyObj('CustomerAccountService', [
      'login',
      'redirectToAccount',
      'redirectToDashboard',
      'getRole'
    ], {
      authSub: new Subject()
    });

    mockTokenStorageService = jasmine.createSpyObj('TokenStorageService', [
      'getToken',
      'saveToken'
    ]);

    mockTotpAuthService = jasmine.createSpyObj('TotpAuthService', [
      'verifyTotp'
    ]);

    mockBrowserService = jasmine.createSpyObj('BrowserService', [
      'open'
    ]);

    mockActivatedRoute = {
      params: of({ socialid: null })
    };

    await TestBed.configureTestingModule({
      imports: [ReactiveFormsModule, LoginComponent, TranslocoTestingModule.forRoot({
        langs: {en},
        translocoConfig: {
          availableLangs: ['en'],
          defaultLang: 'en',
        },
      })],
      providers: [
        FormBuilder,
        { provide: CustomerAccountService, useValue: mockCustomerAccountService },
        { provide: TokenStorageService, useValue: mockTokenStorageService },
        { provide: TotpAuthService, useValue: mockTotpAuthService },
        { provide: BrowserService, useValue: mockBrowserService },
        { provide: ActivatedRoute, useValue: mockActivatedRoute }
      ]
    }).compileComponents();

    fixture = TestBed.createComponent(LoginComponent);
    component = fixture.componentInstance;
  });

  describe('Component Initialization', () => {
    it('should create', () => {
      expect(component).toBeTruthy();
    });

    it('should initialize with correct default values', () => {
      expect(component.submitted).toBe(false);
      expect(component.isLoginFailed).toBe(false);
      expect(component.showTotpInput).toBe(false);
      expect(component.accessToken).toBe('');
    });

    it('should initialize form with correct validators', () => {
      component.ngOnInit();

      expect(component.form).toBeDefined();
      expect(component.form.get('email')?.hasError('required')).toBeTruthy();
      expect(component.form.get('password')?.hasError('required')).toBeTruthy();
      expect(component.form.get('totp')?.value).toBe('');
    });

    it('should set isLoggedIn to true when token exists', () => {
      mockTokenStorageService.getToken.and.returnValue('existing-token');
      component.ngOnInit();
      expect(component.isLoggedIn).toBeTruthy();
    });

    it('should set isLoggedIn to false when no token exists', () => {
      mockTokenStorageService.getToken.and.returnValue(null);
      component.ngOnInit();
      expect(component.isLoggedIn).toBeFalsy();
    });
  });

  describe('Social Login from Route Params', () => {
    it('should handle social login when socialid param exists', () => {
      const socialId = 'social-login-token-123';
      mockActivatedRoute.params = of({ socialid: socialId });
      spyOn(mockCustomerAccountService.authSub, 'next');

      component.ngOnInit();

      expect(mockTokenStorageService.saveToken).toHaveBeenCalledWith(socialId);
      expect(mockCustomerAccountService.authSub.next).toHaveBeenCalledWith('changed');
      expect(mockCustomerAccountService.redirectToAccount).toHaveBeenCalled();
    });

    it('should not process social login when socialid param is null', () => {
      spyOn(mockCustomerAccountService.authSub, 'next');
      component.ngOnInit();

      expect(mockTokenStorageService.saveToken).not.toHaveBeenCalled();
      expect(mockCustomerAccountService.authSub.next).not.toHaveBeenCalled();
    });
  });

  describe('Form Validation', () => {
    beforeEach(() => {
      component.ngOnInit();
    });

    it('should validate email field', () => {
      const emailControl = component.form.get('email');

      // Required validation
      expect(emailControl?.hasError('required')).toBeTruthy();

      // Email format validation
      emailControl?.setValue('invalid-email');
      expect(emailControl?.hasError('email')).toBeTruthy();

      // Valid email
      emailControl?.setValue('valid@example.com');
      expect(emailControl?.hasError('required')).toBeFalsy();
      expect(emailControl?.hasError('email')).toBeFalsy();
    });

    it('should validate password field', () => {
      const passwordControl = component.form.get('password');

      // Required validation
      expect(passwordControl?.hasError('required')).toBeTruthy();

      // Min length validation
      passwordControl?.setValue('12');
      expect(passwordControl?.hasError('minlength')).toBeTruthy();

      // Max length validation
      passwordControl?.setValue('a'.repeat(41));
      expect(passwordControl?.hasError('maxlength')).toBeTruthy();

      // Valid password
      passwordControl?.setValue('validPassword123');
      expect(passwordControl?.hasError('required')).toBeFalsy();
      expect(passwordControl?.hasError('minlength')).toBeFalsy();
      expect(passwordControl?.hasError('maxlength')).toBeFalsy();
    });
  });

  describe('Form Getters', () => {
    beforeEach(() => {
      component.ngOnInit();
    });

    it('should return correct form controls', () => {
      expect(component.email).toBe(component.form.get('email'));
      expect(component.password).toBe(component.form.get('password'));
      expect(component.cf).toBe(component.form.controls);
    });
  });

  describe('onSubmit Method', () => {
    beforeEach(() => {
      component.ngOnInit();
    });

    it('should set submitted to true and return early if form is invalid', () => {
      component.form.patchValue({
        email: 'invalid-email',
        password: ''
      });

      component.onSubmit();

      expect(component.submitted).toBeTruthy();
      expect(mockCustomerAccountService.login).not.toHaveBeenCalled();
    });

    it('should call login service with correct payload when form is valid', () => {
      const mockResponse = {
        requires_totp: false,
        access_token: 'test-token',
        token_type: 'Bearer',
        expires_in: 3600
      };
      mockCustomerAccountService.login.and.returnValue(of(mockResponse));

      component.form.patchValue({
        email: 'test@example.com',
        password: 'password123'
      });

      component.onSubmit();

      expect(mockCustomerAccountService.login).toHaveBeenCalledWith({
        email: 'test@example.com',
        password: 'password123'
      });
    });

    it('should show TOTP input when login requires TOTP', () => {
      const mockResponse = {
        requires_totp: true,
        access_token: 'temp-token',
        token_type: 'Bearer',
        expires_in: 3600
      };
      mockCustomerAccountService.login.and.returnValue(of(mockResponse));

      component.form.patchValue({
        email: 'test@example.com',
        password: 'password123'
      });

      component.onSubmit();

      expect(component.showTotpInput).toBeTruthy();
      expect(component.accessToken).toBe('temp-token');
      expect(component.error).toBeNull();
    });

    it('should handle successful login without TOTP', () => {
      const mockResponse = {
        requires_totp: false,
        access_token: 'final-token',
        token_type: 'Bearer',
        expires_in: 3600
      };
      mockCustomerAccountService.login.and.returnValue(of(mockResponse));
      spyOn(component, 'handleSuccessfulLogin');

      component.form.patchValue({
        email: 'test@example.com',
        password: 'password123'
      });

      component.onSubmit();

      expect(component.handleSuccessfulLogin).toHaveBeenCalledWith('final-token');
    });

    it('should handle login error', () => {
      const mockError = { error: 'Unauthorized' };
      mockCustomerAccountService.login.and.returnValue(throwError(mockError));
      spyOn(component, 'handleLoginError');

      component.form.patchValue({
        email: 'test@example.com',
        password: 'password123'
      });

      component.onSubmit();

      expect(component.handleLoginError).toHaveBeenCalledWith(mockError);
    });
  });

  describe('verifyTotp Method', () => {
    beforeEach(() => {
      component.ngOnInit();
      component.accessToken = 'temp-token';
    });

    it('should show error when TOTP code is empty', () => {
      component.form.patchValue({ totp: '' });

      component.verifyTotp();

      expect(component.error).toBe('TOTP code is required');
      expect(component.isLoginFailed).toBeTruthy();
    });

    it('should call TOTP verification service with correct parameters', () => {
      const mockResponse = { access_token: 'final-token' };
      mockTotpAuthService.verifyTotp.and.returnValue(of(mockResponse));
      component.form.patchValue({ totp: '123456' });

      component.verifyTotp();

      expect(mockTotpAuthService.verifyTotp).toHaveBeenCalledWith('123456', 'temp-token');
    });

    it('should handle successful TOTP verification', () => {
      const mockResponse = { access_token: 'final-token' };
      mockTotpAuthService.verifyTotp.and.returnValue(of(mockResponse));
      spyOn(component, 'handleSuccessfulLogin');
      component.form.patchValue({ totp: '123456' });

      component.verifyTotp();

      expect(component.handleSuccessfulLogin).toHaveBeenCalledWith('final-token');
    });

    it('should handle TOTP verification error', () => {
      const mockError = { error: 'Unauthorized' };
      mockTotpAuthService.verifyTotp.and.returnValue(throwError(mockError));
      spyOn(component, 'handleLoginTOTPError');
      component.form.patchValue({ totp: '123456' });

      component.verifyTotp();

      expect(component.handleLoginTOTPError).toHaveBeenCalledWith(mockError);
    });
  });

  describe('handleSuccessfulLogin Method', () => {
    it('should save token, set login state, and redirect based on role', () => {
      spyOn(mockCustomerAccountService.authSub, 'next');

      // Test user role
      mockCustomerAccountService.getRole.and.returnValue('user');
      component.handleSuccessfulLogin('test-token');

      expect(mockTokenStorageService.saveToken).toHaveBeenCalledWith('test-token');
      expect(component.isLoginFailed).toBeFalsy();
      expect(component.isLoggedIn).toBeTruthy();
      expect(mockCustomerAccountService.authSub.next).toHaveBeenCalledWith('changed');
      expect(mockCustomerAccountService.redirectToAccount).toHaveBeenCalled();

      // Reset and test admin role
      mockCustomerAccountService.redirectToAccount.calls.reset();
      mockCustomerAccountService.getRole.and.returnValue('admin');
      component.handleSuccessfulLogin('test-token');

      expect(mockCustomerAccountService.redirectToDashboard).toHaveBeenCalled();
      expect(mockCustomerAccountService.redirectToAccount).not.toHaveBeenCalled();
    });
  });

  describe('Error Handling', () => {
    it('should handle login errors with appropriate messages', () => {
      const testCases = [
        { error: { error: 'Unauthorized' }, expected: 'Invalid email or password' },
        { error: { error: 'Custom error' }, expected: 'Custom error' },
        { error: {}, expected: 'Login failed' }
      ];

      testCases.forEach(({ error, expected }) => {
        component.handleLoginError(error);
        expect(component.error).toBe(expected);
        expect(component.isLoginFailed).toBeTruthy();
      });
    });

    it('should handle TOTP errors with appropriate messages', () => {
      const testCases = [
        { error: { error: 'Unauthorized' }, expected: 'Invalid TOTP' },
        { error: { error: { error: 'Nested error' } }, expected: 'Nested error' },
        { error: {}, expected: 'Login failed' }
      ];

      testCases.forEach(({ error, expected }) => {
        component.handleLoginTOTPError(error);
        expect(component.error).toBe(expected);
        expect(component.isLoginFailed).toBeTruthy();
      });
    });
  });

  describe('socialLogin Method', () => {
    it('should open browser window with correct URL for different providers', () => {
      const providers = ['google', 'facebook'];

      providers.forEach(provider => {
        component.socialLogin(provider);
        expect(mockBrowserService.open).toHaveBeenCalledWith(
          `https://api.practicesoftwaretesting.com/auth/social-login?provider=${provider}`,
          '',
          'height=500,width=400'
        );
      });
    });
  });

  describe('Integration Tests', () => {
    beforeEach(() => {
      component.ngOnInit();
    });

    it('should complete full login flow with TOTP', () => {
      const loginResponse = {
        requires_totp: true,
        access_token: 'temp-token',
        token_type: 'Bearer',
        expires_in: 3600
      };
      const totpResponse = { access_token: 'final-token' };

      mockCustomerAccountService.login.and.returnValue(of(loginResponse));
      mockTotpAuthService.verifyTotp.and.returnValue(of(totpResponse));
      mockCustomerAccountService.getRole.and.returnValue('admin');
      spyOn(mockCustomerAccountService.authSub, 'next');

      // Initial login
      component.form.patchValue({
        email: 'test@example.com',
        password: 'password123'
      });
      component.onSubmit();

      expect(component.showTotpInput).toBeTruthy();
      expect(component.accessToken).toBe('temp-token');

      // TOTP verification
      component.form.patchValue({ totp: '123456' });
      component.verifyTotp();

      expect(mockTokenStorageService.saveToken).toHaveBeenCalledWith('final-token');
      expect(component.isLoggedIn).toBeTruthy();
      expect(component.isLoginFailed).toBeFalsy();
      expect(mockCustomerAccountService.redirectToDashboard).toHaveBeenCalled();
    });
  });

  describe('Edge Cases', () => {
    beforeEach(() => {
      component.ngOnInit();
    });

    it('should handle empty form submission', () => {
      component.onSubmit();
      expect(component.submitted).toBeTruthy();
      expect(mockCustomerAccountService.login).not.toHaveBeenCalled();
    });

    it('should handle verifyTotp without accessToken', () => {
      mockTotpAuthService.verifyTotp.and.returnValue(of({ access_token: 'final-token' }));
      component.accessToken = '';
      component.form.patchValue({ totp: '123456' });

      component.verifyTotp();

      expect(mockTotpAuthService.verifyTotp).toHaveBeenCalledWith('123456', '');
    });

    it('should handle unknown user role', () => {
      mockCustomerAccountService.getRole.and.returnValue('moderator');
      spyOn(mockCustomerAccountService.authSub, 'next');

      component.handleSuccessfulLogin('test-token');

      expect(mockCustomerAccountService.redirectToAccount).not.toHaveBeenCalled();
      expect(mockCustomerAccountService.redirectToDashboard).not.toHaveBeenCalled();
    });
  });
});
