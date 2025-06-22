import { Component, OnDestroy, OnInit } from '@angular/core';
import { Subject, takeUntil } from 'rxjs';
import { FavoriteService } from "../../_services/favorite.service";
import { Favorite } from "../../models/favorite";
import { RedirectService } from "../../_services/redirect.service";

@Component({
  selector: 'app-favorites',
  templateUrl: './favorites.component.html',
  styleUrls: ['./favorites.component.css']
})
export class FavoritesComponent implements OnInit, OnDestroy {
  favorites: Favorite[] = [];
  private readonly destroy$ = new Subject<void>();

  constructor(
    private readonly favoriteService: FavoriteService,
    private readonly redirectService: RedirectService
  ) {}

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
      localStorage.removeItem('TOKEN_KEY');
      this.redirectService.redirectTo('/auth/login');
    }
  }
}
