import {ComponentFixture, TestBed} from '@angular/core/testing';
import {of, throwError} from 'rxjs';
import {FavoritesComponent} from './favorites.component';
import {FavoriteService} from '../../_services/favorite.service';
import {Favorite} from '../../models/favorite';
import {Product} from '../../models/product';
import {RedirectService} from '../../_services/redirect.service';
import {TokenStorageService} from 'src/app/_services/token-storage.service';
import {TranslocoTestingModule} from "@jsverse/transloco";

describe('FavoritesComponent', () => {
  let component: FavoritesComponent;
  let fixture: ComponentFixture<FavoritesComponent>;
  let favoriteService: jasmine.SpyObj<FavoriteService>;
  let redirectService: jasmine.SpyObj<RedirectService>;
  let tokenStorageService: jasmine.SpyObj<TokenStorageService>;

  const mockProduct: Product = {
    id: '1',
    name: 'Test Product',
    price: 99.99,
    description: 'Mock description',
    in_stock: true,
    is_location_offer: false,
    is_rental: false
  };

  const mockFavorites: Favorite[] = [
    { id: '1', product: { ...mockProduct, id: '1', name: 'Product 1' } },
    { id: '2', product: { ...mockProduct, id: '2', name: 'Product 2' } },
    { id: '3', product: { ...mockProduct, id: '3', name: 'Product 3' } }
  ];

  beforeEach(async () => {
    const favoriteServiceSpy = jasmine.createSpyObj('FavoriteService', ['getFavorites', 'deleteFavorite']);
    const redirectServiceSpy = jasmine.createSpyObj('RedirectService', ['redirectTo']);
    const tokenStorageServiceSpy = jasmine.createSpyObj('TokenStorageService', ['removeToken']);

    await TestBed.configureTestingModule({
      imports: [FavoritesComponent,TranslocoTestingModule.forRoot({
        translocoConfig: {
          availableLangs: ['en'],
          defaultLang: 'en',
        }
      })],
      providers: [
        { provide: FavoriteService, useValue: favoriteServiceSpy },
        { provide: RedirectService, useValue: redirectServiceSpy },
        { provide: TokenStorageService, useValue: tokenStorageServiceSpy }
      ]
    }).compileComponents();

    fixture = TestBed.createComponent(FavoritesComponent);
    component = fixture.componentInstance;
    favoriteService = TestBed.inject(FavoriteService) as jasmine.SpyObj<FavoriteService>;
    redirectService = TestBed.inject(RedirectService) as jasmine.SpyObj<RedirectService>;
    tokenStorageService = TestBed.inject(TokenStorageService) as jasmine.SpyObj<TokenStorageService>;
  });

  beforeEach(() => {
    favoriteService.getFavorites.calls.reset();
    favoriteService.deleteFavorite.calls.reset();
    redirectService.redirectTo.calls.reset();
    tokenStorageService.removeToken.calls.reset();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should initialize with empty favorites array', () => {
    expect(component.favorites).toEqual([]);
  });

  describe('ngOnInit', () => {
    it('should load favorites on initialization', () => {
      favoriteService.getFavorites.and.returnValue(of(mockFavorites));

      component.ngOnInit();

      expect(favoriteService.getFavorites).toHaveBeenCalledTimes(1);
      expect(component.favorites).toEqual(mockFavorites);
    });
  });

  describe('ngOnDestroy', () => {
    it('should complete destroy subject', () => {
      const nextSpy = spyOn(component['destroy$'], 'next');
      const completeSpy = spyOn(component['destroy$'], 'complete');

      component.ngOnDestroy();

      expect(nextSpy).toHaveBeenCalledWith();
      expect(completeSpy).toHaveBeenCalled();
    });
  });

  describe('deleteFavorite', () => {
    it('should delete favorite and reload favorites list', () => {
      const favoriteToDelete = mockFavorites[0];
      const updatedFavorites = mockFavorites.slice(1);

      favoriteService.getFavorites.and.returnValues(
        of(mockFavorites),
        of(updatedFavorites)
      );
      favoriteService.deleteFavorite.and.returnValue(of({}));

      component.ngOnInit();
      component.deleteFavorite(favoriteToDelete);

      expect(favoriteService.deleteFavorite).toHaveBeenCalledWith(favoriteToDelete.id);
      expect(favoriteService.getFavorites).toHaveBeenCalledTimes(2);
      expect(component.favorites).toEqual(updatedFavorites);
    });

    it('should handle delete error with 401 status', () => {
      const favoriteToDelete = mockFavorites[0];
      favoriteService.deleteFavorite.and.returnValue(throwError(() => ({ status: 401 })));

      component.deleteFavorite(favoriteToDelete);

      expect(tokenStorageService.removeToken).toHaveBeenCalled();
      expect(redirectService.redirectTo).toHaveBeenCalledWith('/auth/login');
    });

    it('should handle delete error with 403 status', () => {
      const favoriteToDelete = mockFavorites[0];
      favoriteService.deleteFavorite.and.returnValue(throwError(() => ({ status: 403 })));

      component.deleteFavorite(favoriteToDelete);

      expect(tokenStorageService.removeToken).toHaveBeenCalled();
      expect(redirectService.redirectTo).toHaveBeenCalledWith('/auth/login');
    });

    it('should not redirect on other error statuses', () => {
      const favoriteToDelete = mockFavorites[0];
      favoriteService.deleteFavorite.and.returnValue(throwError(() => ({ status: 500 })));

      component.deleteFavorite(favoriteToDelete);

      expect(tokenStorageService.removeToken).not.toHaveBeenCalled();
      expect(redirectService.redirectTo).not.toHaveBeenCalled();
    });
  });

  describe('loadFavorites', () => {
    it('should load favorites successfully', () => {
      favoriteService.getFavorites.and.returnValue(of(mockFavorites));

      component['loadFavorites']();

      expect(favoriteService.getFavorites).toHaveBeenCalled();
      expect(component.favorites).toEqual(mockFavorites);
    });

    it('should handle load error with 401 status', () => {
      favoriteService.getFavorites.and.returnValue(throwError(() => ({ status: 401 })));

      component['loadFavorites']();

      expect(tokenStorageService.removeToken).toHaveBeenCalled();
      expect(redirectService.redirectTo).toHaveBeenCalledWith('/auth/login');
    });

    it('should handle load error with 403 status', () => {
      favoriteService.getFavorites.and.returnValue(throwError(() => ({ status: 403 })));

      component['loadFavorites']();

      expect(tokenStorageService.removeToken).toHaveBeenCalled();
      expect(redirectService.redirectTo).toHaveBeenCalledWith('/auth/login');
    });

    it('should not redirect on other error statuses during load', () => {
      favoriteService.getFavorites.and.returnValue(throwError(() => ({ status: 500 })));

      component['loadFavorites']();

      expect(tokenStorageService.removeToken).not.toHaveBeenCalled();
      expect(redirectService.redirectTo).not.toHaveBeenCalled();
    });
  });

  describe('handleError', () => {
    it('should handle 401 error', () => {
      component['handleError']({ status: 401 });

      expect(tokenStorageService.removeToken).toHaveBeenCalled();
      expect(redirectService.redirectTo).toHaveBeenCalledWith('/auth/login');
    });

    it('should handle 403 error', () => {
      component['handleError']({ status: 403 });

      expect(tokenStorageService.removeToken).toHaveBeenCalled();
      expect(redirectService.redirectTo).toHaveBeenCalledWith('/auth/login');
    });

    it('should not redirect for other error statuses', () => {
      component['handleError']({ status: 500 });

      expect(tokenStorageService.removeToken).not.toHaveBeenCalled();
      expect(redirectService.redirectTo).not.toHaveBeenCalled();
    });
  });

  describe('subscription management', () => {
    it('should unsubscribe on destroy', () => {
      const nextSpy = spyOn(component['destroy$'], 'next');
      const completeSpy = spyOn(component['destroy$'], 'complete');

      favoriteService.getFavorites.and.returnValue(of(mockFavorites));
      component.ngOnInit();
      component.ngOnDestroy();

      expect(nextSpy).toHaveBeenCalledWith();
      expect(completeSpy).toHaveBeenCalled();
    });
  });
});
