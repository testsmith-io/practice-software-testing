<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

defined('ID_PARAM') || define('ID_PARAM', '/{id}');
defined('SEARCH_PATH') || define('SEARCH_PATH', '/search');
defined('CACHE_HEADERS') || define('CACHE_HEADERS', 'cache.headers:public;max_age=120;etag');
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/status', function () {
    return response()->json(['version' => config('app.version'), 'environment' => env('APP_ENV'), 'app_name' => env('APP_NAME')], 200,
        ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE)->withHeaders([
        'X-CTF-Flag' => 'EXAMPLE',
        'X-CTF-Vulnerability-Description' => 'This is just an example of how the headers will look like if you found something.',
        'X-CTF-Sequence' => '0',
        'X-CTF-Code' => '00000000'
    ]);;
});

Route::get('/logs/laravel.log', function () {
    $logPath = storage_path('logs/laravel.log');

    if (File::exists($logPath)) {
        $logContents = File::get($logPath);
    } else {
        $logContents = 'Log file not found.';
    }

    // CTF Flag: Logs exposed vulnerability - add flag to headers
    return response(nl2br(e($logContents)))->withHeaders([
        'X-CTF-Flag' => 'API8_2023_SECURITY_MISCONFIGURATION_LOG_EXPOSURE',
        'X-CTF-Vulnerability-Description' => 'Application logs are publicly accessible via the web. Logs may contain sensitive information like API keys, user data, and internal system details. This endpoint should be disabled or properly secured.',
        'X-CTF-Sequence' => '10',
        'X-CTF-Code' => '01101001'
    ]);
});

Route::controller(BrandController::class)->prefix('brands')->group(function () {
    Route::middleware(CACHE_HEADERS)->group(function () {
        Route::get('', 'index');
        Route::get(SEARCH_PATH, 'search');
        Route::get(ID_PARAM, 'show');
    });
    Route::post('', 'store');
    Route::put(ID_PARAM, 'update');
    Route::delete(ID_PARAM, 'destroy');
});

Route::controller(CategoryController::class)->prefix('categories')->group(function () {
    Route::middleware(CACHE_HEADERS)->group(function () {
        Route::get('/tree', 'indexTree');
        Route::get('', 'index');
        Route::get(SEARCH_PATH, 'search');
        Route::get(ID_PARAM, 'show');
    });
    Route::post('', 'store');
    Route::put(ID_PARAM, 'update');
    Route::delete(ID_PARAM, 'destroy');
});

Route::controller(ContactController::class)->prefix('messages')->group(function () {
    Route::post('', 'send');
    Route::post('/{id}/attach-file', 'attachFile');
    Route::get('', 'index');
    Route::get(ID_PARAM, 'show');
    Route::post('/{id}/reply', 'storeReply');
    Route::put('/{id}/status', 'updateStatus');
});

Route::controller(FavoriteController::class)->prefix('favorites')->group(function () {
    Route::get('', 'index');
    Route::post('', 'store');
    Route::get(ID_PARAM, 'show');
    Route::delete(ID_PARAM, 'destroy');
});

Route::controller(ImageController::class)->prefix('images')->group(function () {
    Route::middleware(CACHE_HEADERS)->group(function () {
        Route::get('', 'index');
    });
});

Route::controller(InvoiceController::class)->prefix('invoices')->group(function () {
    Route::get('', 'index');
    Route::get(SEARCH_PATH, 'search');
    Route::get(ID_PARAM, 'show');
    Route::put('/{id}/status', 'updateStatus');
    Route::post('', 'store');
    Route::put(ID_PARAM, 'update');
});

Route::controller(PaymentController::class)->prefix('payment')->group(function () {
    Route::post('/check', 'check');
});

Route::controller(ProductController::class)->prefix('products')->group(function () {
    Route::middleware(CACHE_HEADERS)->group(function () {
        Route::get('', 'index');
        Route::get(SEARCH_PATH, 'search');
        Route::get(ID_PARAM, 'show');
        Route::get('/{id}/related', 'showRelated');
    });
    Route::post('', 'store');
    Route::put(ID_PARAM, 'update');
    Route::delete(ID_PARAM, 'destroy');
});

Route::controller(ReportController::class)->prefix('reports')->group(function () {
    Route::get('/total-sales-of-years', 'totalSalesOfYears');
    Route::get('/total-sales-per-country', 'totalSalesPerCountry');
    Route::get('/top10-purchased-products', 'top10PurchasedProducts');
    Route::get('/top10-best-selling-categories', 'top10BestSellingCategories');
    Route::get('/customers-by-country', 'customersByCountry');
    Route::get('/average-sales-per-month', 'averageSalesPerMonth');
    Route::get('/average-sales-per-week', 'averageSalesPerWeek');
});

Route::controller(UserController::class)->prefix('users')->group(function () {
    Route::post('/login', 'login');
    Route::post('/change-password', 'changePassword');
    Route::post('/forgot-password', 'forgotPassword');
    Route::post('/register', 'store');
    Route::get('/logout', 'logout');
    Route::get(SEARCH_PATH, 'search');
    Route::get('/refresh', 'refresh');
    Route::get('/me', 'me');
    Route::put('{id}', 'update');
    Route::get('/', 'index');
    Route::get(ID_PARAM, 'show');
    Route::delete(ID_PARAM, 'destroy');
});

