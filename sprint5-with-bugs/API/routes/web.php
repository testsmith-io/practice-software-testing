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
$router->get('products/search', ['uses' => 'ProductController@search']);
$router->get('products/{id}', ['uses' => 'ProductController@show']);
$router->get('products/{id}/related', ['uses' => 'ProductController@showRelated']);
$router->post('products', ['uses' => 'ProductController@store']);
$router->put('products/{id}', ['uses' => 'ProductController@update']);
$router->delete('products/{id}', ['uses' => 'ProductController@destroy']);

$router->get('brands', ['uses' => 'BrandController@index']);
$router->get('brands/search', ['uses' => 'BrandController@search']);
$router->get('brands/{id}', ['uses' => 'BrandController@show']);
$router->post('brands', ['uses' => 'BrandController@store']);
$router->put('brands/{id}', ['uses' => 'BrandController@update']);
$router->delete('brands/{id}', ['uses' => 'BrandController@destroy']);

$router->get('categories/tree', ['uses' => 'CategoryController@indexTree']);
$router->get('categories', ['uses' => 'CategoryController@index']);
$router->get('categories/search', ['uses' => 'CategoryController@search']);
$router->get('categories/{id}', ['uses' => 'CategoryController@show']);
$router->post('categories', ['uses' => 'CategoryController@store']);
$router->put('categories/{id}', ['uses' => 'CategoryController@update']);
$router->delete('categories/{id}', ['uses' => 'CategoryController@destroy']);

$router->post('messages', ['uses' => 'ContactController@send']);
$router->post('messages/{id}/attach-file', ['uses' => 'ContactController@attachFile']);
$router->get('messages', ['uses' => 'ContactController@index']);
$router->get('messages/{id}', ['uses' => 'ContactController@show']);
$router->post('messages/{id}/reply', ['uses' => 'ContactController@storeReply']);
$router->put('messages/{id}/status', ['uses' => 'ContactController@updateStatus']);

$router->get('images', ['uses' => 'ImageController@index']);

$router->get('invoices', ['uses' => 'InvoiceController@index']);
$router->get('invoices/search', ['uses' => 'InvoiceController@search']);
$router->get('invoices/{id}', ['uses' => 'InvoiceController@show']);
$router->put('invoices/{id}/status', ['uses' => 'InvoiceController@updateStatus']);
$router->post('invoices', ['uses' => 'InvoiceController@store']);
$router->delete('invoices/{id}', ['uses' => 'InvoiceController@destroy']);
$router->put('invoices/{id}', ['uses' => 'InvoiceController@update']);

//$router->get('invoicelines', ['uses' => 'InvoicelineController@index']);
//$router->get('invoicelines/{id}', ['uses' => 'InvoicelineController@show']);
//$router->post('invoicelines', ['uses' => 'InvoicelineController@store']);
//$router->delete('invoicelines/{id}', ['uses' => 'InvoicelineController@destroy']);
//$router->put('invoicelines/{id}', ['uses' => 'InvoicelineController@update']);

$router->get('favorites', ['uses' => 'FavoriteController@index']);
//$router->get('favorites/{id}', ['uses' => 'FavoriteController@show']);
$router->post('favorites', ['uses' => 'FavoriteController@store']);
$router->delete('favorites/{id}', ['uses' => 'FavoriteController@destroy']);
$router->put('favorites/{id}', ['uses' => 'FavoriteController@update']);

$router->group(['prefix' => 'users'], function () use ($router) {
    $router->post('change-password', ['uses' => 'UserController@changePassword']);
    $router->post('forgot-password', ['uses' => 'UserController@forgotPassword']);
    $router->post('register', ['uses' => 'UserController@store']);
    $router->post('login', ['uses' => 'UserController@login']);
    $router->get('logout', ['uses' => 'UserController@logout']);
    $router->get('search', ['uses' => 'UserController@search']);
    $router->get('refresh', ['uses' => 'UserController@refresh']);
    $router->get('me', ['uses' => 'UserController@me']);
    $router->put('{id}', ['uses' => 'UserController@update']);
});

$router->get('users', ['uses' => 'UserController@index']);
//$router->get('customers/search', ['uses' => 'UserController@search']);
$router->get('users/{id}', ['uses' => 'UserController@show']);
//$router->post('customers', ['uses' => 'UserController@store']);
$router->delete('users/{id}', ['uses' => 'UserController@destroy']);
//$router->put('customers/{id}', ['uses' => 'UserController@update']);

$router->post('payment/check', ['uses' => 'PaymentController@check']);

$router->get('reports/total-sales-of-years', ['uses' => 'ReportController@totalSalesOfYears']);
$router->get('reports/total-sales-per-country', ['uses' => 'ReportController@totalSalesPerCountry']);
$router->get('reports/top10-purchased-products', ['uses' => 'ReportController@top10PurchasedProducts']);
$router->get('reports/top10-best-selling-categories', ['uses' => 'ReportController@top10BestSellingCategories']);
$router->get('reports/customers-by-country', ['uses' => 'ReportController@customersByCountry']);
$router->get('reports/average-sales-per-month', ['uses' => 'ReportController@averageSalesPerMonth']);
$router->get('reports/average-sales-per-week', ['uses' => 'ReportController@averageSalesPerWeek']);
