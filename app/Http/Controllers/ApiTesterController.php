<?php

namespace App\Http\Controllers;

/**
 * Halaman API Tester (khusus Super Admin). Permintaan ke API dikirim langsung
 * dari browser (fetch ke /api di domain yang sama), BUKAN lewat server ini,
 * sehingga tidak ada risiko server dipakai menembak alamat lain (SSRF).
 */
class ApiTesterController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasRole('super-admin'), 403, 'Hanya Super Admin.');

        return view('backend.v_api_tester.index', [
            'judul'   => 'API Tester',
            'catalog' => config('api_catalog'),
        ]);
    }
}
