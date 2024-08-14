<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
//        DB::listen(function ($query) {
//            Log::info($query->sql, ['Bindings' => $query->bindings, 'Time' => $query->time]);
//        });

        $this->app['auth']->provider('cached-auth-user',
            function ($app, $config) {
                return new \App\Providers\CachedAuthUserProvider(
                    $this->app['hash'],
                    $config['model']
                );
            });
    }
}
