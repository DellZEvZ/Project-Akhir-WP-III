<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\AsetController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\QuickLoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BarberController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\GaleriController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\FrontCustomerController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RajaOngkirController;

// ========== FRONTEND PUBLIK (Barber Flow) ==========
Route::get('/', [FrontController::class, 'index'])->name('beranda');
Route::get('/layanan', [FrontController::class, 'layanan'])->name('front.layanan');
Route::get('/layanan/{id}', [FrontController::class, 'layananDetail'])->name('front.layanan.detail');
Route::get('/barber', [FrontController::class, 'barber'])->name('front.barber');
Route::get('/galeri', [FrontController::class, 'galeri'])->name('front.galeri');
Route::get('/produk', [FrontController::class, 'produk'])->name('front.produk');
Route::get('/produk/{id}', [FrontController::class, 'produkDetail'])->name('front.produk.detail');

// Katalog gabungan (layanan + produk)
Route::get('/katalog', [FrontController::class, 'catalog'])->name('front.catalog');

// Auth Customer
Route::get('/login', [FrontCustomerController::class, 'loginPage'])->name('customer.login');
Route::post('/login', [FrontCustomerController::class, 'login'])->name('customer.login.post');
Route::post('/register', [FrontCustomerController::class, 'register'])->name('customer.register');
Route::post('/logout', [FrontCustomerController::class, 'logout'])->name('customer.logout');

// Google OAuth
Route::get('/auth/google/redirect', [FrontCustomerController::class, 'googleRedirect'])->name('customer.google.redirect');
Route::get('/auth/google/callback', [FrontCustomerController::class, 'googleCallback'])->name('customer.google.callback');

// Customer-only (is.customer middleware)
Route::middleware('is.customer')->group(function () {
    Route::get('/akun', [FrontCustomerController::class, 'akun'])->name('customer.akun');
    Route::post('/akun/update', [FrontCustomerController::class, 'updateAkun'])->name('customer.akun.update');

    // Booking
    Route::get('/booking', [BookingController::class, 'cart'])->name('booking.cart');
    Route::get('/booking/add/{id}', [BookingController::class, 'add'])->name('booking.add');
    Route::get('/booking/add-produk/{id}', [BookingController::class, 'addProduk'])->name('booking.add.produk');
    Route::post('/booking/update/{itemId}', [BookingController::class, 'update'])->name('booking.update');
    Route::post('/booking/remove/{itemId}', [BookingController::class, 'remove'])->name('booking.remove');
    Route::get('/booking/checkout', [BookingController::class, 'checkout'])->name('booking.checkout');
    Route::get('/booking/slots', [BookingController::class, 'slots'])->name('booking.slots');
    Route::post('/booking/confirm', [BookingController::class, 'confirm'])->name('booking.confirm');

    // Alamat & Ongkos Kirim (RajaOngkir) — khusus order berisi produk
    Route::get('/booking/shipping', [BookingController::class, 'selectShipping'])->name('booking.shipping');
    Route::post('/booking/shipping/update', [BookingController::class, 'updateOngkir'])->name('booking.shipping.update');
    Route::get('/booking/shipping/search', [RajaOngkirController::class, 'searchDestination'])->name('booking.shipping.search');
    Route::post('/booking/shipping/cost', [RajaOngkirController::class, 'getCost'])->name('booking.shipping.cost');

    // Pembayaran
    Route::get('/pembayaran/{id}', [BookingController::class, 'payment'])->name('booking.payment');
    Route::post('/pembayaran/{id}', [BookingController::class, 'pay'])->name('booking.pay');

    // Gateway mitra (simulasi) → konfirmasi → lunas
    Route::get('/pembayaran/{id}/gateway', [BookingController::class, 'gateway'])->name('booking.gateway');
    Route::post('/pembayaran/{id}/konfirmasi', [BookingController::class, 'payConfirm'])->name('booking.pay.confirm');

    // Struk / bukti pembayaran
    Route::get('/struk/{id}', [BookingController::class, 'struk'])->name('booking.struk');

    // Pembelian produk
    Route::post('/produk/{id}/beli', [BookingController::class, 'buyProduk'])->name('produk.beli');
});

Route::get('backend/beranda', [BerandaController::class, 'berandaBackend'])
    ->name('backend.beranda')
    ->middleware('auth');

Route::get('backend/login', [LoginController::class, 'loginBackend'])
    ->name('backend.login');

Route::post('backend/login', [LoginController::class, 'authenticateBackend']);

// Alias untuk route 'login' (Laravel default) -> diarahkan ke login admin.
// Path dibuat unik agar tidak bentrok dengan '/login' milik customer.
Route::get('admin-login', function () {
    return redirect()->route('backend.login');
})->name('login');

Route::post('backend/logout', [LoginController::class, 'logoutBackend'])
    ->name('backend.logout');

// ========== QUICK LOGIN API (Testing Only - No Auth Required) ==========
// Login admin tanpa password: rute HANYA didaftarkan di environment lokal.
// Tidak cukup bergantung pada APP_DEBUG, karena variabel itu bisa tertimpa
// oleh konfigurasi server dan membuka akses admin ke siapa pun.
if (app()->isLocal()) {
    Route::get('api/quick-login/users', [QuickLoginController::class, 'getUsersList'])
        ->name('api.quick-login.users');
    Route::post('backend/quick-login/{userId}', [QuickLoginController::class, 'loginAs'])
        ->name('backend.quick-login.login-as');
}

Route::resource('backend/user', UserController::class, ['as' => 'backend'])
    ->middleware('auth');

Route::get('backend/laporan/formuser', [UserController::class, 'formUser'])
    ->name('backend.laporan.formuser')
    ->middleware('auth');

Route::post('backend/laporan/cetakuser', [UserController::class, 'cetakUser'])
    ->name('backend.laporan.cetakuser')
    ->middleware('auth');

// Role-Permission Management Routes (Super Admin only)
Route::middleware(['auth'])->group(function () {
    Route::get('backend/roles/{roleId}/permissions', [UserController::class, 'getRolePermissions'])
        ->name('backend.roles.permissions.get');
    Route::put('backend/roles/{roleId}/permissions', [UserController::class, 'updateRolePermissions'])
        ->name('backend.roles.permissions.update');
});

Route::get('backend/laporan/formproduk', [ProdukController::class, 'formProduk'])
    ->name('backend.laporan.formproduk')
    ->middleware('auth');

Route::post('backend/laporan/cetakproduk', [ProdukController::class, 'cetakProduk'])
    ->name('backend.laporan.cetakproduk')
    ->middleware('auth');

Route::resource('backend/kategori', KategoriController::class, ['as' => 'backend'])
    ->middleware('auth'); 

Route::resource('backend/produk', ProdukController::class, ['as' => 'backend'])
    ->middleware('auth');

// Route untuk menambahkan foto
Route::post('foto-produk/store', [ProdukController::class, 'storeFoto'])
    ->name('backend.foto_produk.store')
    ->middleware('auth');

// Route untuk menghapus foto
Route::delete('foto-produk/{id}', [ProdukController::class, 'destroyFoto'])
    ->name('backend.foto_produk.destroy')
    ->middleware('auth');

// ========== PEGAWAI ROUTES ==========
Route::middleware(['auth'])->group(function () {
    // Resource routes untuk Pegawai
    Route::resource('backend/pegawai', PegawaiController::class, ['as' => 'backend']);

    // Laporan Pegawai
    Route::get('backend/laporan/formpegawai', [PegawaiController::class, 'formPegawai'])
        ->name('backend.laporan.formpegawai');
    Route::post('backend/laporan/cetakpegawai', [PegawaiController::class, 'cetakPegawai'])
        ->name('backend.laporan.cetakpegawai');

    // Statistik Pegawai
    Route::get('backend/pegawai-statistik', [PegawaiController::class, 'statistik'])
        ->name('backend.pegawai.statistik');
});

// ========== ASET ROUTES ==========
Route::middleware(['auth'])->group(function () {
    // Resource routes untuk Aset
    Route::resource('backend/aset', AsetController::class, ['as' => 'backend']);

    // Laporan Aset
    Route::get('backend/laporan/formaset', [AsetController::class, 'formAset'])
        ->name('backend.laporan.formaset');
    Route::post('backend/laporan/cetakaset', [AsetController::class, 'cetakAset'])
        ->name('backend.laporan.cetakaset');

    // Statistik Aset
    Route::get('backend/aset-statistik', [AsetController::class, 'statistik'])
        ->name('backend.aset.statistik');

    // Maintenance Aset
    Route::post('backend/aset/{id}/maintenance', [AsetController::class, 'updateMaintenance'])
        ->name('backend.aset.maintenance.update');
    Route::get('backend/aset-maintenance/list', [AsetController::class, 'maintenanceList'])
        ->name('backend.aset.maintenance.list');

    // ========== SETTING ROUTES ==========
    Route::get('backend/setting/sistem', [SettingController::class, 'sistem'])
        ->name('backend.setting.sistem');
    Route::post('backend/setting/sistem', [SettingController::class, 'updateSistem'])
        ->name('backend.setting.sistem.update');

    // Backup & Restore Routes
    Route::get('backend/setting/backup', [App\Http\Controllers\BackupController::class, 'index'])
        ->name('backend.setting.backup');
    Route::post('backend/setting/backup/create', [App\Http\Controllers\BackupController::class, 'create'])
        ->name('backend.setting.backup.create');
    Route::get('backend/setting/backup/download/{filename}', [App\Http\Controllers\BackupController::class, 'download'])
        ->name('backend.setting.backup.download');
    Route::delete('backend/setting/backup/delete/{filename}', [App\Http\Controllers\BackupController::class, 'destroy'])
        ->name('backend.setting.backup.delete');
    Route::post('backend/setting/backup/restore/{filename}', [App\Http\Controllers\BackupController::class, 'restore'])
        ->name('backend.setting.backup.restore');
    Route::post('backend/setting/backup/upload', [App\Http\Controllers\BackupController::class, 'upload'])
        ->name('backend.setting.backup.upload');

    Route::get('backend/setting/log', [SettingController::class, 'log'])
        ->name('backend.setting.log');
    Route::get('backend/setting/akun', [SettingController::class, 'akun'])
        ->name('backend.setting.akun');
    Route::get('backend/setting/bantuan', [SettingController::class, 'bantuan'])
        ->name('backend.setting.bantuan');

    // ========== MESSAGES & NOTIFICATIONS ROUTES ==========
    Route::get('backend/notifikasi', [SettingController::class, 'notifikasi'])
        ->name('backend.notifikasi');
    Route::get('backend/pesan', [SettingController::class, 'pesan'])
        ->name('backend.pesan');

    // ========== PROFIL ROUTES ==========
    Route::get('backend/profil', [ProfilController::class, 'index'])
        ->name('backend.profil.index');
    Route::post('backend/profil/update', [ProfilController::class, 'updateProfil'])
        ->name('backend.profil.update');
    Route::post('backend/profil/password', [ProfilController::class, 'updatePassword'])
        ->name('backend.profil.password');

    // ========== BARBERSHOP ROUTES ==========
    Route::resource('backend/barber', BarberController::class, ['as' => 'backend']);
    Route::resource('backend/layanan', LayananController::class, ['as' => 'backend']);
    Route::resource('backend/galeri', GaleriController::class, ['as' => 'backend'])->only(['index','create','store','destroy']);

    // Traffic & Aktivitas (supervisor/pemilik)
    Route::get('backend/aktivitas', [\App\Http\Controllers\ActivityController::class, 'index'])->name('backend.aktivitas.index');

    // Manajemen Booking (admin)
    Route::get('backend/order', [OrderController::class, 'index'])->name('backend.order.index');
    Route::get('backend/order/{order}', [OrderController::class, 'show'])->name('backend.order.show');
    Route::put('backend/order/{order}/status', [OrderController::class, 'updateStatus'])->name('backend.order.status');
    Route::put('backend/order/{order}/verify', [OrderController::class, 'verifyPayment'])->name('backend.order.verify');

    // ========== ATTENDANCE ROUTES (Manual - No GPS) ==========
    // Pegawai Routes
    Route::prefix('backend/attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::post('/checkin', [AttendanceController::class, 'checkIn'])->name('checkin');
        Route::post('/checkout', [AttendanceController::class, 'checkOut'])->name('checkout');
        Route::get('/history', [AttendanceController::class, 'history'])->name('history');
        Route::get('/export', [AttendanceController::class, 'export'])->name('export');
    });

    // Admin/Supervisor Routes for Attendance Management
    Route::prefix('backend/attendance/admin')->name('attendance.admin.')->group(function () {
        Route::get('/', [AttendanceController::class, 'adminIndex'])->name('index');
        Route::get('/{id}/edit', [AttendanceController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AttendanceController::class, 'update'])->name('update');
        Route::post('/{id}/approve', [AttendanceController::class, 'approve'])->name('approve');
        Route::delete('/{id}', [AttendanceController::class, 'destroy'])->name('destroy');
    });
});