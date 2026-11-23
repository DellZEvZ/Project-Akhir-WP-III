<?php

namespace App\Providers;

use App\Http\Controllers\Api\CatalogApiController;
use App\Models\Barber;
use App\Models\Galeri;
use App\Models\Kategori;
use App\Models\Layanan;
use App\Models\Produk;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Batas laju login/registrasi API: 10 percobaan per menit per IP klien.
        // Di balik Cloudflare, ip() berisi IP edge (dipakai bersama semua orang),
        // jadi kuncinya CF-Connecting-IP bila valid.
        RateLimiter::for('auth-api', function (Request $request) {
            $cf = $request->header('CF-Connecting-IP');
            $ip = ($cf && filter_var($cf, FILTER_VALIDATE_IP)) ? $cf : $request->ip();

            return Limit::perMinute(10)->by($ip)->response(function (Request $request, array $headers) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak percobaan. Coba lagi dalam beberapa saat.',
                ], 429, $headers);
            });
        });

        // Polling status reset password dari app (tiap ~4 detik): 40 per menit per IP.
        RateLimiter::for('reset-status', function (Request $request) {
            $cf = $request->header('CF-Connecting-IP');
            $ip = ($cf && filter_var($cf, FILTER_VALIDATE_IP)) ? $cf : $request->ip();

            return Limit::perMinute(40)->by($ip)->response(function (Request $request, array $headers) {
                return response()->json(['success' => false, 'message' => 'Terlalu banyak permintaan. Coba lagi sebentar.'], 429, $headers);
            });
        });

        // Data katalog berubah (lewat panel admin) -> cache katalog API dibuat usang.
        foreach ([Layanan::class, Produk::class, Barber::class, Galeri::class, Kategori::class] as $model) {
            // afterCommit: cache baru tidak boleh terisi ulang dengan data lama
            // sebelum transaksi selesai.
            $model::saved(fn () => DB::afterCommit(fn () => CatalogApiController::forgetCache()));
            $model::deleted(fn () => DB::afterCommit(fn () => CatalogApiController::forgetCache()));
        }

        // Register custom Blade directive for permission check
        Blade::if('hasPermission', function ($permission) {
            return auth()->check() && auth()->user()->hasPermission($permission);
        });

        // Register custom Blade directive for role check
        Blade::if('hasRole', function ($role) {
            return auth()->check() && auth()->user()->hasRole($role);
        });
    }
}
