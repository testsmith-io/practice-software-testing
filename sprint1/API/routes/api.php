<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

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

/**
 * @OA\Get(
 *      path="/status",
 *      operationId="getStatus",
 *      tags={"Status"},
 *      summary="Retrieve application status",
 *      description="Returns application metadata (version, environment, app name).",
 *      @OA\Response(
 *          response=200,
 *          description="Successful operation",
 *          @OA\JsonContent(
 *              type="object",
 *              @OA\Property(property="version", type="string"),
 *              @OA\Property(property="environment", type="string"),
 *              @OA\Property(property="app_name", type="string")
 *          )
 *      )
 * )
 */
Route::get('/status', function () {
    return response()->json(['version' => config('app.version'), 'environment' => env('APP_ENV'), 'app_name' => env('APP_NAME')], 200,
        ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
});

Route::middleware('cache.headers:public;max_age=120;etag')->group(function () {
    Route::controller(BrandController::class)->prefix('brands')->group(function () {
        Route::get('', 'index');
        Route::get('/{id}', 'show');
    });

    Route::controller(CategoryController::class)->prefix('categories')->group(function () {
        Route::get('/tree', 'indexTree');
        Route::get('', 'index');
        Route::get('/tree/{id}', 'show');
    });

    Route::controller(ImageController::class)->prefix('images')->group(function () {
        Route::get('', 'index');
    });

    Route::controller(ProductController::class)->prefix('products')->group(function () {
        Route::get('', 'index');
        Route::get('/{id}', 'show');
        Route::get('/{id}/related', 'showRelated');
    });
});

Route::controller(BrandController::class)->prefix('brands')->group(function () {
    Route::post('', 'store');
    Route::put('/{id}', 'update');
    Route::delete('/{id}', 'destroy');
});

Route::controller(CategoryController::class)->prefix('categories')->group(function () {
    Route::post('', 'store');
    Route::put('/{id}', 'update');
    Route::delete('/{id}', 'destroy');
});

Route::controller(ProductController::class)->prefix('products')->group(function () {
    Route::post('', 'store');
    Route::put('/{id}', 'update');
    Route::delete('/{id}', 'destroy');
});

