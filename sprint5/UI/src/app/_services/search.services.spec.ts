// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {TestBed} from '@angular/core/testing';
import {provideHttpClient} from '@angular/common/http';
import {HttpTestingController, provideHttpClientTesting} from '@angular/common/http/testing';
import {BrandService} from './brand.service';
import {CategoryService} from './category.service';
import {InvoiceService} from './invoice.service';
import {UserService} from './user.service';
import {environment} from '../../environments/environment';

// Search/filter calls use the HTTP QUERY method (RFC 10008): the criteria
// travel in a JSON body instead of the URL query string.
describe('QUERY-based search endpoints', () => {
  let httpMock: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()]
    });
    httpMock = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    httpMock.verify();
  });

  it('BrandService.searchBrands should use QUERY with the term in the body', () => {
    TestBed.inject(BrandService).searchBrands('forge').subscribe();

    const req = httpMock.expectOne(`${environment.apiUrl}/brands/search`);
    expect(req.request.method).toBe('QUERY');
    expect(req.request.headers.get('Content-Type')).toBe('application/json');
    expect(req.request.body).toEqual({q: 'forge'});
    req.flush([]);
  });

  it('CategoryService.searchCategories should use QUERY with the term in the body', () => {
    TestBed.inject(CategoryService).searchCategories('tools').subscribe();

    const req = httpMock.expectOne(`${environment.apiUrl}/categories/search`);
    expect(req.request.method).toBe('QUERY');
    expect(req.request.body).toEqual({q: 'tools'});
    req.flush([]);
  });

  it('CategoryService.getSubCategoriesTreeBySlug should use QUERY with the slug in the body', () => {
    TestBed.inject(CategoryService).getSubCategoriesTreeBySlug('hand-tools').subscribe();

    const req = httpMock.expectOne(`${environment.apiUrl}/categories/tree`);
    expect(req.request.method).toBe('QUERY');
    expect(req.request.body).toEqual({by_category_slug: 'hand-tools'});
    req.flush([]);
  });

  it('CategoryService.getCategoriesTree should keep using GET when there are no criteria', () => {
    TestBed.inject(CategoryService).getCategoriesTree().subscribe();

    const req = httpMock.expectOne(`${environment.apiUrl}/categories/tree`);
    expect(req.request.method).toBe('GET');
    req.flush([]);
  });

  it('InvoiceService.searchInvoices should use QUERY with page and term in the body', () => {
    TestBed.inject(InvoiceService).searchInvoices(2, 'INV-101').subscribe();

    const req = httpMock.expectOne(`${environment.apiUrl}/invoices/search`);
    expect(req.request.method).toBe('QUERY');
    expect(req.request.body).toEqual({page: '2', q: 'INV-101'});
    req.flush({data: []});
  });

  it('UserService.searchUsers should use QUERY with page and term in the body', () => {
    TestBed.inject(UserService).searchUsers(1, 'jane').subscribe();

    const req = httpMock.expectOne(`${environment.apiUrl}/users/search`);
    expect(req.request.method).toBe('QUERY');
    expect(req.request.body).toEqual({page: '1', q: 'jane'});
    req.flush({data: []});
  });
});
