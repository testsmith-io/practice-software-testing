<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PostcodeController;
use App\Http\Controllers\ProductSpecController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SocialConnectController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\TOTPController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

const REFRESH = '/refresh';
const CACHE_HEADERS = 'cache.headers:public;max_age=120;etag';
const SEARCH = '/search';
const ID_PARAM = '/{id}';
const TREE = '/tree';
const ID_STATUS = '/{id}/status';
const SPECS = '/products/{productId}/specs';
const SPEC_ID = '/products/{productId}/specs/{specId}';

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

// Shared OPTIONS handler: computes the Allow header from whichever other
// routes match this request's path (ignoring the HTTP method itself), so
// the list can't drift out of sync with the GET/POST/QUERY/etc. routes
// defined below — even when a sibling route uses a differently named
// path parameter (e.g. carts' {id} vs {cartId}).
$respondOptions = function (\Illuminate\Http\Request $request) {
    $allowed = collect(Route::getRoutes())
        ->filter(fn ($route) => $route->matches($request, false))
        ->flatMap(fn ($route) => $route->methods())
        ->reject(fn ($method) => in_array($method, ['HEAD', 'OPTIONS']))
        ->unique()
        ->sort()
        ->values()
        ->implode(', ');

    return response()->noContent(204)->header('Allow', $allowed);
};

Route::get('/status', function () {
    return response()->json(['version' => config('app.version'), 'environment' => env('APP_ENV'), 'app_name' => env('APP_NAME')], 200,
        ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE
    );
});

Route::post(REFRESH, function () {
    Artisan::call('migrate:fresh', [
        '--seed' => null
    ]);

    Artisan::call('invoice:remove');

    // The DB is wiped — flush all caches so we don't serve stale records
    // pointing to IDs that no longer exist after the seed.
    Cache::flush();

    return response()->json(['result' => 'refresh done']);
});

Route::options('/status', $respondOptions);
Route::options(REFRESH, $respondOptions);

Route::controller(BrandController::class)->prefix('brands')->group(function () use ($respondOptions) {
    Route::middleware(CACHE_HEADERS)->group(function () {
        Route::get('', 'index');
        Route::get(SEARCH, 'search');
        Route::get(ID_PARAM, 'show');
    });
    Route::match(['QUERY'], SEARCH, 'search')->middleware('query.body');
    Route::post('', 'store');
    Route::put(ID_PARAM, 'update');
    Route::patch(ID_PARAM, 'patch');
    Route::delete(ID_PARAM, 'destroy');
    Route::options('', $respondOptions);
    Route::options(SEARCH, $respondOptions);
    Route::options(ID_PARAM, $respondOptions);
});

Route::controller(CartController::class)->prefix('carts')->group(function () use ($respondOptions) {
    Route::post('', 'createCart');
    Route::post(ID_PARAM, 'addItem');
    Route::put('/{id}/product/quantity', 'updateQuantity');
    Route::get(ID_PARAM, 'getCart');
    Route::delete('/{cartId}/product/{productId}', 'removeProductFromCart');
    Route::delete('/{cartId}', 'deleteCart');
    Route::options('', $respondOptions);
    Route::options(ID_PARAM, $respondOptions);
    Route::options('/{id}/product/quantity', $respondOptions);
    Route::options('/{cartId}/product/{productId}', $respondOptions);
});

Route::controller(CategoryController::class)->prefix('categories')->group(function () use ($respondOptions) {
    Route::middleware(CACHE_HEADERS)->group(function () {
        Route::get(TREE, 'indexTree');
        Route::get('', 'index');
        Route::get(SEARCH, 'search');
        Route::get('/tree/{id}', 'show');
    });
    Route::middleware('query.body')->group(function () {
        Route::match(['QUERY'], TREE, 'indexTree');
        Route::match(['QUERY'], SEARCH, 'search');
    });
    Route::post('', 'store');
    Route::patch(ID_PARAM, 'patch');
    Route::put(ID_PARAM, 'update');
    Route::delete(ID_PARAM, 'destroy');
    Route::options('', $respondOptions);
    Route::options(TREE, $respondOptions);
    Route::options(SEARCH, $respondOptions);
    Route::options('/tree/{id}', $respondOptions);
    Route::options(ID_PARAM, $respondOptions);
});

Route::get('/postcode-lookup', [PostcodeController::class, 'lookup']);
Route::options('/postcode-lookup', $respondOptions);

Route::controller(ContactController::class)->prefix('messages')->group(function () use ($respondOptions) {
    Route::post('', 'send');
    Route::post('/{id}/attach-file', 'attachFile');
    Route::get('', 'index');
    Route::get(ID_PARAM, 'show');
    Route::post('/{id}/reply', 'storeReply');
    Route::put(ID_STATUS, 'updateStatus');
    Route::options('', $respondOptions);
    Route::options(ID_PARAM, $respondOptions);
    Route::options('/{id}/attach-file', $respondOptions);
    Route::options('/{id}/reply', $respondOptions);
    Route::options(ID_STATUS, $respondOptions);
});

Route::controller(FavoriteController::class)->prefix('favorites')->group(function () use ($respondOptions) {
    Route::get('', 'index');
    Route::post('', 'store');
    Route::get(ID_PARAM, 'show');
    Route::delete(ID_PARAM, 'destroy');
    Route::options('', $respondOptions);
    Route::options(ID_PARAM, $respondOptions);
});

Route::controller(ImageController::class)->prefix('images')->group(function () use ($respondOptions) {
    Route::middleware(CACHE_HEADERS)->group(function () {
        Route::get('', 'index');
    });
    Route::options('', $respondOptions);
});

Route::controller(InvoiceController::class)->prefix('invoices')->group(function () use ($respondOptions) {
    Route::get('', 'index');
    Route::get(SEARCH, 'search');
    Route::match(['QUERY'], SEARCH, 'search')->middleware('query.body');
    Route::get(ID_PARAM, 'show');
    Route::get('/{id}/download-pdf', 'downloadPDF');
    Route::get('/{id}/download-pdf-status', 'downloadPDFStatus');
    Route::put(ID_STATUS, 'updateStatus');
    Route::post('', 'store');
    Route::post('/guest', 'storeGuest');
    Route::put(ID_PARAM, 'update');
    Route::patch(ID_PARAM, 'patch');
    Route::options('', $respondOptions);
    Route::options(SEARCH, $respondOptions);
    Route::options('/guest', $respondOptions);
    Route::options(ID_PARAM, $respondOptions);
    Route::options('/{id}/download-pdf', $respondOptions);
    Route::options('/{id}/download-pdf-status', $respondOptions);
    Route::options(ID_STATUS, $respondOptions);
});

Route::controller(PaymentController::class)->prefix('payment')->group(function () use ($respondOptions) {
    Route::post('/check', 'check');
    Route::options('/check', $respondOptions);
});

Route::controller(ProductController::class)->prefix('products')->group(function () use ($respondOptions) {
    Route::middleware(CACHE_HEADERS)->group(function () {
        Route::get('', 'index');
        Route::get(SEARCH, 'search');
        Route::get(ID_PARAM, 'show');
        Route::get('/{id}/related', 'showRelated');
    });
    Route::middleware('query.body')->group(function () {
        Route::match(['QUERY'], '', 'index');
        Route::match(['QUERY'], SEARCH, 'search');
    });
    Route::post('', 'store');
    Route::put(ID_PARAM, 'update');
    Route::patch(ID_PARAM, 'patch');
    Route::delete(ID_PARAM, 'destroy');
    Route::options('', $respondOptions);
    Route::options(SEARCH, $respondOptions);
    Route::options(ID_PARAM, $respondOptions);
    Route::options('/{id}/related', $respondOptions);
});

// Live sales feed over Server-Sent Events (SSE). Public, finite, seedable.
Route::get('/sales-stream', [StreamController::class, 'sales']);

Route::controller(ProductSpecController::class)->group(function () use ($respondOptions) {
    Route::middleware(CACHE_HEADERS)->group(function () {
        Route::get(SPECS, 'index');
        Route::get(SPEC_ID, 'show');
        Route::get('/product-specs/names', 'specNames');
    });
    Route::post(SPECS, 'store');
    Route::put(SPEC_ID, 'update');
    Route::delete(SPEC_ID, 'destroy');
    Route::options(SPECS, $respondOptions);
    Route::options(SPEC_ID, $respondOptions);
    Route::options('/product-specs/names', $respondOptions);
});

Route::middleware(['throttle:reports'])->controller(ReportController::class)->prefix('reports')->group(function () use ($respondOptions) {
    Route::get('/total-sales-of-years', 'totalSalesOfYears');
    Route::get('/total-sales-per-country', 'totalSalesPerCountry');
    Route::get('/top10-purchased-products', 'top10PurchasedProducts');
    Route::get('/top10-best-selling-categories', 'top10BestSellingCategories');
    Route::get('/customers-by-country', 'customersByCountry');
    Route::get('/average-sales-per-month', 'averageSalesPerMonth');
    Route::get('/average-sales-per-week', 'averageSalesPerWeek');
    Route::options('/total-sales-of-years', $respondOptions);
    Route::options('/total-sales-per-country', $respondOptions);
    Route::options('/top10-purchased-products', $respondOptions);
    Route::options('/top10-best-selling-categories', $respondOptions);
    Route::options('/customers-by-country', $respondOptions);
    Route::options('/average-sales-per-month', $respondOptions);
    Route::options('/average-sales-per-week', $respondOptions);
});

Route::controller(UserController::class)->prefix('users')->group(function () use ($respondOptions) {
    Route::post('/login', 'login');
    Route::post('/change-password', 'changePassword');
    Route::post('/forgot-password', 'forgotPassword');
    Route::post('/register', 'store');
    Route::get('/logout', 'logout');
    Route::get(SEARCH, 'search');
    Route::match(['QUERY'], SEARCH, 'search')->middleware('query.body');
    Route::get(REFRESH, 'refresh');
    Route::get('/me', 'me');
    Route::put('{id}', 'update');
    Route::patch('{id}', 'patch');
    Route::get('/', 'index');
    Route::get(ID_PARAM, 'show');
    Route::delete(ID_PARAM, 'destroy');
    Route::options('/', $respondOptions);
    Route::options('/login', $respondOptions);
    Route::options('/change-password', $respondOptions);
    Route::options('/forgot-password', $respondOptions);
    Route::options('/register', $respondOptions);
    Route::options('/logout', $respondOptions);
    Route::options(SEARCH, $respondOptions);
    Route::options(REFRESH, $respondOptions);
    Route::options('/me', $respondOptions);
    Route::options(ID_PARAM, $respondOptions);
});

Route::controller(SocialConnectController::class)->prefix('auth')->group(function () use ($respondOptions) {
    Route::get('/social-login', 'getAuthUrl');
    Route::get('/cb/google', 'callbackGoogle');
    Route::get('/cb/github', 'callbackGithub');
    Route::options('/social-login', $respondOptions);
    Route::options('/cb/google', $respondOptions);
    Route::options('/cb/github', $respondOptions);
});

Route::controller(TOTPController::class)->prefix('totp')->group(function () use ($respondOptions) {
    Route::post('/setup', 'setup');
    Route::post('/verify', 'verify');
    Route::post('/login/totp', 'loginWithTOTP');
    Route::options('/setup', $respondOptions);
    Route::options('/verify', $respondOptions);
    Route::options('/login/totp', $respondOptions);
});
