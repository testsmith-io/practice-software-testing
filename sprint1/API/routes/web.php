<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/status', function () use ($router) {
    return response()->json(['version' => $router->app->version(), 'environment' => env('APP_ENV'), 'app_name' => env('APP_NAME')], 200,
        ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
});

$router->get('products', ['uses' => 'ProductController@index']);
$router->get('products/{id}', ['uses' => 'ProductController@show']);
$router->get('products/{id}/related', ['uses' => 'ProductController@showRelated']);
$router->post('products', ['uses' => 'ProductController@store']);
$router->put('products/{id}', ['uses' => 'ProductController@update']);
$router->delete('products/{id}', ['uses' => 'ProductController@destroy']);

$router->get('brands', ['uses' => 'BrandController@index']);
$router->get('brands/{id}', ['uses' => 'BrandController@show']);
$router->post('brands', ['uses' => 'BrandController@store']);
$router->put('brands/{id}', ['uses' => 'BrandController@update']);
$router->delete('brands/{id}', ['uses' => 'BrandController@destroy']);

$router->get('categories/tree', ['uses' => 'CategoryController@indexTree']);
$router->get('categories', ['uses' => 'CategoryController@index']);
$router->get('categories/{id}', ['uses' => 'CategoryController@show']);
$router->post('categories', ['uses' => 'CategoryController@store']);
$router->put('categories/{id}', ['uses' => 'CategoryController@update']);
$router->delete('categories/{id}', ['uses' => 'CategoryController@destroy']);

$router->get('images', ['uses' => 'ImageController@index']);
