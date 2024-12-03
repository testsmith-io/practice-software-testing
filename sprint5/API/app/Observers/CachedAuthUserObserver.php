<?php

namespace App\Observers;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Cache;

class CachedAuthUserObserver
{
    private int $savedTtl = 300;
    private int $restoredTtl = 300;
    private int $retrievedTtl = 300;

    public function saved(User $user): void
    {
        Cache::put("auth.user.{$user->id}", $user, $this->savedTtl);
    }

    public function deleted(User $user): void
    {
        Cache::forget("auth.user.{$user->id}");
    }

    public function updated(User $user): void
    {
        Cache::forget("auth.user.{$user->id}");
    }

    public function restored(User $user): void
    {
        Cache::put("auth.user.{$user->id}", $user, $this->restoredTtl);
    }

    public function retrieved(User $user): void
    {
        Cache::add("auth.user.{$user->id}", $user, $this->retrievedTtl);
    }
}
