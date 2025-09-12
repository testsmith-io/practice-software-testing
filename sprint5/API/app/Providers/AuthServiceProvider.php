<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\User;
use App\Policies\ProductPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        User::class => UserPolicy::class,
    ];
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
