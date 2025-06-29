import {Component, inject, OnInit} from '@angular/core';
import {first} from "rxjs/operators";
import {FavoriteService} from "../../_services/favorite.service";
import {Favorite} from "../../models/favorite";
import {TruncatePipe} from "../../_helpers/truncate.pipe";

@Component({
  selector: 'app-favorites',
  templateUrl: './favorites.component.html',
  imports: [
    TruncatePipe
  ],
  styleUrls: ['./favorites.component.css']
})
export class FavoritesComponent implements OnInit {
  private readonly favoriteService = inject(FavoriteService);
  favorites!: Favorite[];

  ngOnInit(): void {
    this.getFavorites();
  }

  deleteFavorite(favorite: Favorite) {
    this.favoriteService.deleteFavorite(favorite.product.id)
      .pipe(first())
      .subscribe(() => {
        this.getFavorites();
      });
  }

  getFavorites() {
    this.favoriteService.getFavorites()
      .pipe(first())
      .subscribe((favorites) => {
          this.favorites = favorites
        },
        (error) => {
          if (error.status === 401 || error.status === 403) {
            window.localStorage.removeItem('TOKEN_KEY');
            window.location.href = '/#/auth/login';
          }
        });
  }
}
