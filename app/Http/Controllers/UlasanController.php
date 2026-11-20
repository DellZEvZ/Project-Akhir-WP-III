<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\Ulasan;
use Illuminate\Http\Request;

/** Moderasi ulasan dari app mobile (daftar, filter rating, hapus). */
class UlasanController extends Controller
{
    public function index(Request $request)
    {
        $ulasans = Ulasan::with(['customer', 'order.orderItems.layanan'])
            ->when($request->filled('rating'), fn ($q) => $q->where('rating', (int) $request->rating))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(function ($qq) use ($s) {
                    $qq->where('komentar', 'like', "%{$s}%")
                       ->orWhereHas('customer', fn ($c) => $c->where('nama', 'like', "%{$s}%"));
                });
            })
            ->latest()->paginate(20)->withQueryString();

        return view('backend.v_ulasan.index', [
            'judul'   => 'Ulasan Pelanggan',
            'ulasans' => $ulasans,
            'rata'    => round((float) Ulasan::avg('rating'), 1),
            'total'   => Ulasan::count(),
        ]);
    }

    public function destroy(Ulasan $ulasan)
    {
        $nama = $ulasan->customer?->nama ?? 'Customer #' . $ulasan->customer_id;

        ActivityLogger::log('delete', 'ulasan', "Admin menghapus ulasan dari {$nama}", $ulasan, [
            'customer_id' => $ulasan->customer_id,
            'rating'      => $ulasan->rating,
            'komentar'    => $ulasan->komentar,
        ]);
        $ulasan->delete();

        return redirect()->route('backend.ulasan.index')->with('success', 'Ulasan berhasil dihapus.');
    }
}
