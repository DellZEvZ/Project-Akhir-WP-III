<?php

namespace App\Providers;

use App\Http\Controllers\Api\CatalogApiController;
use App\Models\Barber;
use App\Models\Galeri;
use App\Models\Kategori;
use App\Models\Layanan;
use App\Models\Produk;
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
