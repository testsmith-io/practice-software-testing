<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;


class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
//        DB::listen(function ($query) {
//            Log::info($query->sql, ['Bindings' => $query->bindings, 'Time' => $query->time]);
//        });

        $this->app['auth']->provider('cached-auth-user',
            function ($app, $config) {
                return new CachedAuthUserProvider(
                    $this->app['hash'],
                    $config['model']
                );
            }
        );
    }
}
