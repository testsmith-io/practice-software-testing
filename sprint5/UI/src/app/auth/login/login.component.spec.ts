import { ComponentFixture, TestBed } from '@angular/core/testing';
import { FormBuilder, ReactiveFormsModule } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { of, throwError, Subject } from 'rxjs';

import { LoginComponent } from './login.component';
import { CustomerAccountService } from '../../shared/customer-account.service';
import { TokenStorageService } from '../../_services/token-storage.service';
import { TotpAuthService } from '../../_services/totp-auth.service';
import { BrowserService } from '../../_services/browser.service';

describe('LoginComponent', () => {
  let component: LoginComponent;
  let fixture: ComponentFixture<LoginComponent>;
  let mockCustomerAccountService: jasmine.SpyObj<CustomerAccountService>;
  let mockTokenStorageService: jasmine.SpyObj<TokenStorageService>;
  let mockTotpAuthService: jasmine.SpyObj<TotpAuthService>;
  let mockBrowserService: jasmine.SpyObj<BrowserService>;
  let mockActivatedRoute: any;

  beforeEach(async () => {
    // Create spy objects for all services
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
      declarations: [LoginComponent],
      imports: [ReactiveFormsModule],
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

    it('should initialize form with correct validators', () => {
      component.ngOnInit();

      expect(component.form).toBeDefined();
      expect(component.form.get('email')?.hasError('required')).toBeTruthy();
      expect(component.form.get('password')?.hasError('required')).toBeTruthy();
      expect(component.form.get('totp')).toBeDefined();
    });

    it('should initialize submitted as false', () => {
      // This test verifies the initial false value of submitted
      expect(component.submitted).toBe(false);

      // Initialize the form first
      component.ngOnInit();

      // Set up valid form data before calling onSubmit
      component.form.patchValue({
        email: 'test@example.com',
        password: 'password123'
      });

      // Mock the login service to prevent actual API call
      const mockResponse = { requires_totp: false, access_token: 'token' };
      mockCustomerAccountService.login.and.returnValue(of(mockResponse));

      // Trigger onSubmit to change it to true
      component.onSubmit();
      expect(component.submitted).toBe(true);
    });

    it('should initialize isLoginFailed as false', () => {
      // This test verifies the initial false value of isLoginFailed
      expect(component.isLoginFailed).toBe(false);

      // Trigger an error to change it to true
      const error = { error: 'Test error' };
      component.handleLoginError(error);
      expect(component.isLoginFailed).toBe(true);
    });

    it('should initialize showTotpInput as false', () => {
      // This test verifies the initial false value of showTotpInput
      expect(component.showTotpInput).toBe(false);

      // Initialize the form first
      component.ngOnInit();

      // Simulate login response that requires TOTP
      const mockResponse = { requires_totp: true, access_token: 'temp-token' };
      mockCustomerAccountService.login.and.returnValue(of(mockResponse));

      component.form.patchValue({
        email: 'test@example.com',
        password: 'password123'
      });

      component.onSubmit();
      expect(component.showTotpInput).toBe(true);
    });

    it('should initialize accessToken as empty string', () => {
      // This test verifies the initial empty string value of accessToken
      expect(component.accessToken).toBe('');

      // Initialize the form first
      component.ngOnInit();

      // Simulate login response that sets accessToken
      const mockResponse = { requires_totp: true, access_token: 'temp-token' };
      mockCustomerAccountService.login.and.returnValue(of(mockResponse));

      component.form.patchValue({
        email: 'test@example.com',
        password: 'password123'
      });

      component.onSubmit();
      expect(component.accessToken).toBe('temp-token');
    });

    it('should set isLoggedIn to true if token exists', () => {
      mockTokenStorageService.getToken.and.returnValue('existing-token');

      component.ngOnInit();

      expect(component.isLoggedIn).toBeTruthy();
    });

    it('should set isLoggedIn to false if no token exists', () => {
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
      mockActivatedRoute.params = of({ socialid: null });
      spyOn(mockCustomerAccountService.authSub, 'next');

      component.ngOnInit();

      expect(mockTokenStorageService.saveToken).not.toHaveBeenCalled();
      expect(mockCustomerAccountService.authSub.next).not.toHaveBeenCalled();
      expect(mockCustomerAccountService.redirectToAccount).not.toHaveBeenCalled();
    });
  });

  describe('Form Validation', () => {
    beforeEach(() => {
      component.ngOnInit();
    });

    it('should validate email field as required', () => {
      const emailControl = component.form.get('email');

      expect(emailControl?.hasError('required')).toBeTruthy();

      emailControl?.setValue('test@example.com');
      expect(emailControl?.hasError('required')).toBeFalsy();
    });

    it('should validate email format', () => {
      const emailControl = component.form.get('email');

      emailControl?.setValue('invalid-email');
      expect(emailControl?.hasError('email')).toBeTruthy();

      emailControl?.setValue('valid@example.com');
      expect(emailControl?.hasError('email')).toBeFalsy();
    });

    it('should validate password as required', () => {
      const passwordControl = component.form.get('password');

      expect(passwordControl?.hasError('required')).toBeTruthy();

      passwordControl?.setValue('password123');
      expect(passwordControl?.hasError('required')).toBeFalsy();
    });

    it('should validate password minimum length', () => {
      const passwordControl = component.form.get('password');

      passwordControl?.setValue('12');
      expect(passwordControl?.hasError('minlength')).toBeTruthy();

      passwordControl?.setValue('123');
      expect(passwordControl?.hasError('minlength')).toBeFalsy();
    });

    it('should validate password maximum length', () => {
      const passwordControl = component.form.get('password');
      const longPassword = 'a'.repeat(41);

      passwordControl?.setValue(longPassword);
      expect(passwordControl?.hasError('maxlength')).toBeTruthy();

      passwordControl?.setValue('validPassword123');
      expect(passwordControl?.hasError('maxlength')).toBeFalsy();
    });
  });

  describe('Form Getters', () => {
    beforeEach(() => {
      component.ngOnInit();
    });

    it('should return email control', () => {
      expect(component.email).toBe(component.form.get('email'));
    });

    it('should return password control', () => {
      expect(component.password).toBe(component.form.get('password'));
    });

    it('should return form controls object', () => {
      expect(component.cf).toBe(component.form.controls);
    });
  });

  describe('onSubmit Method', () => {
    beforeEach(() => {
      component.ngOnInit();
    });

    it('should set submitted to true', () => {
      component.onSubmit();
      expect(component.submitted).toBeTruthy();
    });

    it('should return early if form is invalid', () => {
      component.form.patchValue({
        email: 'invalid-email',
        password: ''
      });

      component.onSubmit();

      expect(mockCustomerAccountService.login).not.toHaveBeenCalled();
    });

    it('should call login service with correct payload when form is valid', () => {
      const mockResponse = { requires_totp: false, access_token: 'test-token' };
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

    it('should initialize totp form control with empty string', () => {
      // This test verifies the initial empty string value in totp form control
      const totpControl = component.form.get('totp');
      expect(totpControl?.value).toBe('');

      // Change the value to verify it can be modified
      totpControl?.setValue('123456');
      expect(totpControl?.value).toBe('123456');
    });

    it('should validate totp field behavior with empty value', () => {
      // Test that empty TOTP value is handled correctly in verifyTotp
      component.accessToken = 'temp-token';
      component.form.patchValue({ totp: '' });

      component.verifyTotp();

      expect(component.error).toBe('TOTP code is required');
      expect(component.isLoginFailed).toBe(true);
    });

    it('should show TOTP input when login requires TOTP', () => {
      const mockResponse = {
        requires_totp: true,
        access_token: 'temp-token'
      };
      mockCustomerAccountService.login.and.returnValue(of(mockResponse));

      component.form.patchValue({
        email: 'test@example.com',
        password: 'password123'
      });

      component.onSubmit();

      expect(component.showTotpInput).toBeTruthy();
      expect(component.error).toBeNull();
      expect(component.accessToken).toBe('temp-token');
    });

    it('should handle successful login without TOTP', () => {
      const mockResponse = {
        requires_totp: false,
        access_token: 'final-token'
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
    it('should save token and set login state', () => {
      spyOn(mockCustomerAccountService.authSub, 'next');

      component.handleSuccessfulLogin('test-token');

      expect(mockTokenStorageService.saveToken).toHaveBeenCalledWith('test-token');
      expect(component.isLoginFailed).toBeFalsy();
      expect(component.isLoggedIn).toBeTruthy();
      expect(mockCustomerAccountService.authSub.next).toHaveBeenCalledWith('changed');
    });

    it('should redirect to account for user role', () => {
      mockCustomerAccountService.getRole.and.returnValue('user');
      spyOn(mockCustomerAccountService.authSub, 'next');

      component.handleSuccessfulLogin('test-token');

      expect(mockCustomerAccountService.redirectToAccount).toHaveBeenCalled();
      expect(mockCustomerAccountService.redirectToDashboard).not.toHaveBeenCalled();
    });

    it('should redirect to dashboard for admin role', () => {
      mockCustomerAccountService.getRole.and.returnValue('admin');
      spyOn(mockCustomerAccountService.authSub, 'next');

      component.handleSuccessfulLogin('test-token');

      expect(mockCustomerAccountService.redirectToDashboard).toHaveBeenCalled();
      expect(mockCustomerAccountService.redirectToAccount).not.toHaveBeenCalled();
    });
  });

  describe('handleLoginError Method', () => {
    it('should set error message for Unauthorized error', () => {
      const error = { error: 'Unauthorized' };

      component.handleLoginError(error);

      expect(component.error).toBe('Invalid email or password');
      expect(component.isLoginFailed).toBeTruthy();
    });

    it('should set custom error message', () => {
      const error = { error: 'Custom error message' };

      component.handleLoginError(error);

      expect(component.error).toBe('Custom error message');
      expect(component.isLoginFailed).toBeTruthy();
    });

    it('should set default error message when no specific error', () => {
      const error = {};

      component.handleLoginError(error);

      expect(component.error).toBe('Login failed');
      expect(component.isLoginFailed).toBeTruthy();
    });
  });

  describe('handleLoginTOTPError Method', () => {
    it('should set error message for TOTP Unauthorized error', () => {
      const error = { error: 'Unauthorized' };

      component.handleLoginTOTPError(error);

      expect(component.error).toBe('Invalid TOTP');
      expect(component.isLoginFailed).toBeTruthy();
    });

    it('should set nested error message', () => {
      const error = { error: { error: 'Nested error message' } };

      component.handleLoginTOTPError(error);

      expect(component.error).toBe('Nested error message');
      expect(component.isLoginFailed).toBeTruthy();
    });

    it('should set default error message when no specific error', () => {
      const error = {};

      component.handleLoginTOTPError(error);

      expect(component.error).toBe('Login failed');
      expect(component.isLoginFailed).toBeTruthy();
    });
  });

  describe('socialLogin Method', () => {
    it('should open browser window with correct URL for Google provider', () => {
      component.socialLogin('google');

      expect(mockBrowserService.open).toHaveBeenCalledWith(
        'https://api.practicesoftwaretesting.com/auth/social-login?provider=google',
        '',
        'height=500,width=400'
      );
    });

    it('should open browser window with correct URL for Facebook provider', () => {
      component.socialLogin('facebook');

      expect(mockBrowserService.open).toHaveBeenCalledWith(
        'https://api.practicesoftwaretesting.com/auth/social-login?provider=facebook',
        '',
        'height=500,width=400'
      );
    });
  });

  describe('Integration Tests', () => {
    beforeEach(() => {
      component.ngOnInit();
    });

    it('should complete full login flow without TOTP', () => {
      const mockResponse = { requires_totp: false, access_token: 'final-token' };
      mockCustomerAccountService.login.and.returnValue(of(mockResponse));
      mockCustomerAccountService.getRole.and.returnValue('user');
      spyOn(mockCustomerAccountService.authSub, 'next');

      component.form.patchValue({
        email: 'test@example.com',
        password: 'password123'
      });

      component.onSubmit();

      expect(component.submitted).toBeTruthy();
      expect(mockTokenStorageService.saveToken).toHaveBeenCalledWith('final-token');
      expect(component.isLoggedIn).toBeTruthy();
      expect(component.isLoginFailed).toBeFalsy();
      expect(mockCustomerAccountService.redirectToAccount).toHaveBeenCalled();
    });

    it('should complete full login flow with TOTP', () => {
      const loginResponse = { requires_totp: true, access_token: 'temp-token' };
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
      const mockResponse = { access_token: 'final-token' };
      mockTotpAuthService.verifyTotp.and.returnValue(of(mockResponse));
      component.accessToken = '';
      component.form.patchValue({ totp: '123456' });

      component.verifyTotp();

      expect(mockTotpAuthService.verifyTotp).toHaveBeenCalledWith('123456', '');
    });

    it('should handle role that is neither user nor admin', () => {
      mockCustomerAccountService.getRole.and.returnValue('moderator');
      spyOn(mockCustomerAccountService.authSub, 'next');

      component.handleSuccessfulLogin('test-token');

      expect(mockCustomerAccountService.redirectToAccount).not.toHaveBeenCalled();
      expect(mockCustomerAccountService.redirectToDashboard).not.toHaveBeenCalled();
    });
  });

  describe('State Transition Coverage', () => {
    beforeEach(() => {
      component.ngOnInit(); // Initialize form before all tests in this describe block
    });

    it('should transition submitted from false to true only on form submission', () => {
      // Verify initial false state
      expect(component.submitted).toBe(false);

      // Set up accessToken for verifyTotp call
      component.accessToken = 'temp-token';

      // Various actions that should NOT change submitted
      component.verifyTotp();
      expect(component.submitted).toBe(false);

      component.handleLoginError({ error: 'test' });
      expect(component.submitted).toBe(false);

      component.handleSuccessfulLogin('token');
      expect(component.submitted).toBe(false);

      // Set up valid form data and mock service for onSubmit
      component.form.patchValue({
        email: 'test@example.com',
        password: 'password123'
      });
      const mockResponse = { requires_totp: false, access_token: 'token' };
      mockCustomerAccountService.login.and.returnValue(of(mockResponse));

      // Reset submitted to false for this test
      component.submitted = false;

      // Only onSubmit should change it to true
      component.onSubmit();
      expect(component.submitted).toBe(true);
    });

    it('should transition isLoginFailed from false to true only on errors', () => {
      // Verify initial false state
      expect(component.isLoginFailed).toBe(false);

      // Actions that should NOT change isLoginFailed to true
      component.onSubmit();
      expect(component.isLoginFailed).toBe(false);

      component.handleSuccessfulLogin('token');
      expect(component.isLoginFailed).toBe(false);

      // Actions that SHOULD change it to true
      component.handleLoginError({ error: 'test' });
      expect(component.isLoginFailed).toBe(true);

      // Reset and test TOTP error
      component.isLoginFailed = false;
      component.handleLoginTOTPError({ error: 'test' });
      expect(component.isLoginFailed).toBe(true);
    });

    it('should transition showTotpInput from false to true only when TOTP is required', () => {
      // Verify initial false state
      expect(component.showTotpInput).toBe(false);

      // Set up valid form data
      component.form.patchValue({
        email: 'test@example.com',
        password: 'password123'
      });

      // Login without TOTP requirement should keep it false
      const mockResponseNoTotp = { requires_totp: false, access_token: 'token' };
      mockCustomerAccountService.login.and.returnValue(of(mockResponseNoTotp));

      component.onSubmit();
      expect(component.showTotpInput).toBe(false);

      // Reset submitted state for next test
      component.submitted = false;

      // Login WITH TOTP requirement should change it to true
      const mockResponseWithTotp = { requires_totp: true, access_token: 'temp-token' };
      mockCustomerAccountService.login.and.returnValue(of(mockResponseWithTotp));

      component.onSubmit();
      expect(component.showTotpInput).toBe(true);
    });

    it('should handle accessToken transitions from empty string', () => {
      // Verify initial empty string state
      expect(component.accessToken).toBe('');

      // Set up valid form data
      component.form.patchValue({
        email: 'test@example.com',
        password: 'password123'
      });

      // Successful login with TOTP requirement should set accessToken
      const mockResponse = { requires_totp: true, access_token: 'temp-token-123' };
      mockCustomerAccountService.login.and.returnValue(of(mockResponse));

      component.onSubmit();
      expect(component.accessToken).toBe('temp-token-123');
      expect(component.accessToken).not.toBe(''); // Ensure it changed from empty string
    });
  });

  // Test different error scenarios to ensure boolean mutations are killed
  describe('Error State Mutations', () => {
    it('should handle multiple error scenarios affecting isLoginFailed', () => {
      // Test different error types that should all result in isLoginFailed = true
      const errorScenarios = [
        { error: 'Unauthorized' },
        { error: 'Invalid credentials' },
        { error: { error: 'Nested error' } },
        {} // Empty error object
      ];

      errorScenarios.forEach((error, index) => {
        // Reset state
        component.isLoginFailed = false;

        if (index % 2 === 0) {
          component.handleLoginError(error);
        } else {
          component.handleLoginTOTPError(error);
        }

        expect(component.isLoginFailed).toBe(true);
      });
    });

    it('should verify successful login resets isLoginFailed to false', () => {
      // Set error state first
      component.isLoginFailed = true;

      // Successful login should reset it to false
      component.handleSuccessfulLogin('test-token');
      expect(component.isLoginFailed).toBe(false);
    });
  });
});
