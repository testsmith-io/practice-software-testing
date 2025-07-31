import {Component, inject, OnDestroy, OnInit} from '@angular/core';
import {Subject, takeUntil} from 'rxjs';
import {FavoriteService} from "../../_services/favorite.service";
import {Favorite} from "../../models/favorite";
import {RedirectService} from "../../_services/redirect.service";
import {RenderDelayDirective} from "../../render-delay-directive.directive";
import {TruncatePipe} from "../../_helpers/truncate.pipe";
import {FaIconComponent} from "@fortawesome/angular-fontawesome";
import {TranslocoDirective} from "@jsverse/transloco";
import {TokenStorageService} from "../../_services/token-storage.service";

@Component({
  selector: 'app-favorites',
  templateUrl: './favorites.component.html',
  imports: [
    RenderDelayDirective,
    TruncatePipe,
    FaIconComponent,
    TranslocoDirective
  ],
  styleUrls: ['./favorites.component.css']
})
export class FavoritesComponent implements OnInit, OnDestroy {
  private readonly favoriteService = inject(FavoriteService);
  private readonly redirectService = inject(RedirectService);
  private readonly tokenStorage = inject(TokenStorageService);

  favorites: Favorite[] = [];
  private readonly destroy$ = new Subject<void>();

  ngOnInit(): void {
    this.loadFavorites();
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  deleteFavorite(favorite: Favorite): void {
    this.favoriteService.deleteFavorite(favorite.id)
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: () => this.loadFavorites(),
        error: (error) => this.handleError(error)
      });
  }

  private loadFavorites(): void {
    this.favoriteService.getFavorites()
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (favorites) => this.favorites = favorites,
        error: (error) => this.handleError(error)
      });
  }

  private handleError(error: any): void {
    if (error.status === 401 || error.status === 403) {
      this.tokenStorage.removeToken();
      this.redirectService.redirectTo('/auth/login');
    }
  }
}
