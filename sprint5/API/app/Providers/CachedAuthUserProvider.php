<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Support\Facades\Cache;

class CachedAuthUserProvider extends EloquentUserProvider
{
    public function __construct(HasherContract $hasher)
    {
        parent::__construct($hasher, User::class);
    }

    public function retrieveById($identifier)
    {
        $cacheKey = "auth.user.$identifier";

        if (Cache::has($cacheKey)) {
//            Log::info("User {$identifier} retrieved from cache.");
            return Cache::get($cacheKey);
        } else {
//            Log::info("User {$identifier} retrieved from database.");
            $user = parent::retrieveById($identifier);
            Cache::put($cacheKey, $user, now()->addMinutes(10)); // Cache for 10 minutes
            return $user;
        }
    }
}
