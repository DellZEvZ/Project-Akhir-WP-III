<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CatalogApiController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\BookingApiController;

// ===== Publik (tanpa token) =====
Route::get('/layanan',      [CatalogApiController::class, 'layanan']);
Route::get('/layanan/{id}', [CatalogApiController::class, 'layananShow']);
Route::get('/produk',       [CatalogApiController::class, 'produk']);
Route::get('/produk/{id}',  [CatalogApiController::class, 'produkShow']);
Route::get('/barber',       [CatalogApiController::class, 'barber']);
Route::get('/galeri',       [CatalogApiController::class, 'galeri']);

// Auth customer
Route::post('/register', [CustomerApiController::class, 'register']);
Route::post('/login',    [CustomerApiController::class, 'login']);
Route::post('/login/google', [CustomerApiController::class, 'google']);

// ===== Terproteksi (Bearer token) =====
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [CustomerApiController::class, 'logout']);
    Route::get('/me',      [CustomerApiController::class, 'me']);
    Route::post('/me',     [CustomerApiController::class, 'update']);

    Route::get('/booking',          [BookingApiController::class, 'index']);
    Route::post('/booking',         [BookingApiController::class, 'store']);
    Route::post('/booking/produk',  [BookingApiController::class, 'storeProduk']);
    Route::get('/booking/{id}',     [BookingApiController::class, 'show']);
    Route::put('/booking/{id}',     [BookingApiController::class, 'update']);
    Route::delete('/booking/{id}',  [BookingApiController::class, 'destroy']);
    Route::post('/booking/{id}/pay', [BookingApiController::class, 'pay']);

    Route::get('/shipping/search',  [BookingApiController::class, 'searchShipping']);
    Route::post('/shipping/cost',   [BookingApiController::class, 'shippingCost']);
});
