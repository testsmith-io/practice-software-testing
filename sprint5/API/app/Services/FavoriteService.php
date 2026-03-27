<?php

namespace App\Services;

use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FavoriteService
{
    public function getAllFavorites()
    {
        $userId = Auth::id();
        Log::debug('Fetching all favorites for user', ['user_id' => $userId]);

        $favorites = Favorite::with('product', 'product.product_image')
            ->where('user_id', $userId)
            ->get();

        Log::debug('Favorites fetched', ['count' => $favorites->count()]);

        return $favorites;
    }

    public function createFavorite(array $data)
    {
        $userId = Auth::id();
        $data['user_id'] = $userId;

        Log::debug('Creating favorite', ['user_id' => $userId, 'data' => $data]);

        $favorite = Favorite::create($data);

        Log::info('Favorite created', ['favorite_id' => $favorite->id]);

        return $favorite;
    }

    public function getFavoriteById($id)
    {
        Log::debug('Fetching favorite by ID', ['favorite_id' => $id]);

        $favorite = Favorite::findOrFail($id);

        Log::debug('Favorite found', ['favorite' => $favorite]);

        return $favorite;
    }

    public function deleteFavorite($favoriteId)
    {
        $userId = Auth::id();

        Log::debug('Attempting to delete favorite', [
            'user_id' => $userId,
            'favorite_id' => $favoriteId
        ]);

        $deleted = Favorite::where('user_id', $userId)
            ->where('id', $favoriteId)
            ->delete();

        Log::info('Favorite delete operation completed', [
            'user_id' => $userId,
            'favorite_id' => $favoriteId,
            'deleted' => $deleted
        ]);

        return $deleted;
    }
}
