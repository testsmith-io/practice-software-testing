<?php

namespace App\Providers;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(ResponseFactory::class, function () {
            return new \Laravel\Lumen\Http\ResponseFactory();
        });

        //Event::listen('Illuminate\Database\Events\QueryExecuted', function ($query) {
        // filter oauth ones
//            if (!str_contains($query->sql, 'oauth')) {
        //       Log::debug($query->sql . ' - ' . serialize($query->bindings));
//            }
        // });
    }
}
