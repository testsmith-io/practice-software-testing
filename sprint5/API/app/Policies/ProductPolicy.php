<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    public function viewStock(?User $user, Product $product): bool
    {
        if (!$user) {
            return false; // Non-authenticated users see boolean stock status
        }

        return $user->role === 'admin';
    }

    public function viewAdminData(?User $user): bool
    {
        return $user && $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Product $product): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->role === 'admin';
    }
}