// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {TestBed} from '@angular/core/testing';
import {provideHttpClient} from '@angular/common/http';
import {HttpTestingController, provideHttpClientTesting} from '@angular/common/http/testing';
import {ProductService} from './product.service';
import {environment} from '../../environments/environment';

describe('ProductService', () => {
  let service: ProductService;
  let httpMock: HttpTestingController;
  const apiURL = `${environment.apiUrl}/products`;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()]
    });
    service = TestBed.inject(ProductService);
    httpMock = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    httpMock.verify();
  });

  it('should fetch products with a QUERY request carrying the page', () => {
    service.getProducts(2).subscribe();

    const req = httpMock.expectOne(apiURL);
    expect(req.request.method).toBe('QUERY');
    expect(req.request.headers.get('Content-Type')).toBe('application/json');
    expect(req.request.body).toEqual({page: '2'});
    req.flush({data: []});
  });

  it('should send all filter criteria in the QUERY body', () => {
    service.getProductsNew('hammer', 'price,asc', '10', '50', '1,2', '3', 1, true, false, 'cordless').subscribe();

    const req = httpMock.expectOne(apiURL);
    expect(req.request.method).toBe('QUERY');
    expect(req.request.body).toEqual({
      page: '1',
      q: 'hammer',
      sort: 'price,asc',
      between: 'price,10,50',
      by_category: '1,2',
      by_brand: '3',
      eco_friendly: 'true',
      is_rental: 'false',
      by_spec: 'cordless'
    });
    req.flush({data: []});
  });

  it('should omit empty filter criteria from the QUERY body', () => {
    service.getProductsNew('', '', '', '', '', '', 1, false, null).subscribe();

    const req = httpMock.expectOne(apiURL);
    expect(req.request.body).toEqual({page: '1'});
    req.flush({data: []});
  });

  it('should fetch rentals with a QUERY request', () => {
    service.getProductRentals().subscribe();

    const req = httpMock.expectOne(apiURL);
    expect(req.request.method).toBe('QUERY');
    expect(req.request.body).toEqual({is_rental: 'true'});
    req.flush({data: []});
  });

  it('should fetch products by category slug with a QUERY request', () => {
    service.getProductsByCategory('hand-tools', 3).subscribe();

    const req = httpMock.expectOne(apiURL);
    expect(req.request.method).toBe('QUERY');
    expect(req.request.body).toEqual({page: '3', by_category_slug: 'hand-tools'});
    req.flush({data: []});
  });

  it('should search products with a QUERY request', () => {
    service.searchProducts('pliers').subscribe();

    const req = httpMock.expectOne(`${apiURL}/search`);
    expect(req.request.method).toBe('QUERY');
    expect(req.request.body).toEqual({q: 'pliers'});
    req.flush({data: []});
  });

  it('should fetch products by category and brand with a QUERY request', () => {
    service.getProductsByCategoryAndBrand('1', '2', 'name,asc').subscribe();

    const req = httpMock.expectOne(apiURL);
    expect(req.request.method).toBe('QUERY');
    expect(req.request.body).toEqual({by_category: '1', by_brand: '2', sort: 'name,asc'});
    req.flush({data: []});
  });

  it('should keep using GET for a single product', () => {
    service.getProduct('01HGR').subscribe();
    const req = httpMock.expectOne(`${apiURL}/01HGR`);
    expect(req.request.method).toBe('GET');
    req.flush({});
  });
});
