<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CatalogApiController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\BookingApiController;
use App\Http\Controllers\Api\UlasanApiController;

// ===== Publik (tanpa token) =====
Route::get('/layanan',      [CatalogApiController::class, 'layanan']);
Route::get('/layanan/{id}', [CatalogApiController::class, 'layananShow']);
Route::get('/produk',       [CatalogApiController::class, 'produk']);
Route::get('/produk/{id}',  [CatalogApiController::class, 'produkShow']);
Route::get('/barber',       [CatalogApiController::class, 'barber']);
Route::get('/galeri',       [CatalogApiController::class, 'galeri']);

// Auth customer
Route::middleware('throttle:auth-api')->group(function () {
    Route::post('/register', [CustomerApiController::class, 'register']);
    Route::post('/login',    [CustomerApiController::class, 'login']);
    Route::post('/login/google', [CustomerApiController::class, 'google']);
    // Lupa password: ajukan permintaan -> admin menyetujui di panel -> password sementara
    Route::post('/password/forgot', [CustomerApiController::class, 'forgotPassword']);
});
// App menanyakan status permintaannya tiap beberapa detik, jadi batasnya lebih longgar.
Route::post('/password/status', [CustomerApiController::class, 'passwordStatus'])->middleware('throttle:reset-status');

// ===== Terproteksi (Bearer token) =====
// EnsurePasswordChanged: selama masih memakai password sementara, hanya /me, /me/password, /logout yang boleh.
Route::middleware(['auth:sanctum', \App\Http\Middleware\EnsurePasswordChanged::class])->group(function () {
    Route::post('/logout', [CustomerApiController::class, 'logout']);
    Route::get('/me',      [CustomerApiController::class, 'me']);
    Route::post('/me',     [CustomerApiController::class, 'update']);
    Route::delete('/me',   [CustomerApiController::class, 'destroy']);
    Route::post('/me/password', [CustomerApiController::class, 'changePassword'])->middleware('throttle:auth-api');

    Route::get('/booking',          [BookingApiController::class, 'index']);
    Route::post('/booking',         [BookingApiController::class, 'store']);
    Route::post('/booking/produk',  [BookingApiController::class, 'storeProduk']);
    Route::get('/booking/{id}',     [BookingApiController::class, 'show']);
    Route::put('/booking/{id}',     [BookingApiController::class, 'update']);
    Route::delete('/booking/{id}',  [BookingApiController::class, 'destroy']);
    Route::post('/booking/{id}/pay', [BookingApiController::class, 'pay']);

    // Ulasan layanan (booking berstatus done)
    Route::get('/ulasan',           [UlasanApiController::class, 'index']);
    Route::post('/ulasan',          [UlasanApiController::class, 'store']);
    Route::get('/ulasan/{id}',      [UlasanApiController::class, 'show']);
    Route::put('/ulasan/{id}',      [UlasanApiController::class, 'update']);
    Route::delete('/ulasan/{id}',   [UlasanApiController::class, 'destroy']);

    Route::get('/shipping/search',  [BookingApiController::class, 'searchShipping']);
    Route::post('/shipping/cost',   [BookingApiController::class, 'shippingCost']);
});
