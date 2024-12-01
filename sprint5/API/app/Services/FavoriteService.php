<?php

namespace App\Services;

use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;

class FavoriteService
{
    public function getAllFavorites()
    {
        return Favorite::with('product', 'product.product_image')
            ->where('user_id', Auth::user()->id)
            ->get();
    }

    public function createFavorite(array $data)
    {
        $data['user_id'] = Auth::user()->id;
        return Favorite::create($data);
    }

    public function getFavoriteById($id)
    {
        return Favorite::findOrFail($id);
    }

    public function deleteFavorite($productId)
    {
        return Favorite::where('user_id', Auth::user()->id)
            ->where('product_id', $productId)
            ->delete();
    }
}
