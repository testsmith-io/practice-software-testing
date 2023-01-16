<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{

    use DatabaseMigrations;
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    protected function headers($user = null): array
    {
        $headers = ['Content-Type' => 'application/json',
            'Accept' => 'application/json'];

        if (!is_null($user)) {
            $token = app('auth')->fromUser($user);
//            dd($token);
            $headers['Authorization'] = 'Bearer '.$token;
//            dd($headers);
        }

        return $headers;
    }
}
