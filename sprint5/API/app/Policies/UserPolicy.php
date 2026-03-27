<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAdminData(?User $currentUser, User $targetUser): bool
    {
        return $currentUser && $currentUser->role === 'admin';
    }

    public function update(User $currentUser, User $targetUser): bool
    {
        // Users can update their own profile, admins can update any user
        return $currentUser->id === $targetUser->id || $currentUser->role === 'admin';
    }

    public function delete(User $currentUser, User $targetUser): bool
    {
        return $currentUser->role === 'admin';
    }

    public function viewAll(User $user): bool
    {
        return $user->role === 'admin';
    }
}