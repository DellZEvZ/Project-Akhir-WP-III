<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /** Batas baris baru per sekali polling (cegah balasan raksasa setelah tab lama tidak aktif). */
    private const FEED_LIMIT = 50;

    /** Terapkan filter modul/aksi yang sama untuk daftar utama dan feed realtime. */
    private function filtered(Request $request)
    {
        $query = ActivityLog::with('user');

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }
        if ($request->filled('action')) {
            $query->where('action_type', $request->action);
        }

        return $query;
    }

    /**
     * Semua angka kartu statistik dalam SATU query (database berlatensi tinggi,
     * tiap query ~1 detik).
     */
    private function stats(): array
    {
        $r = ActivityLog::query()->selectRaw(
            "COALESCE(SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END), 0) AS hari_ini,
             COALESCE(SUM(CASE WHEN module = 'pelanggan' THEN 1 ELSE 0 END), 0) AS pelanggan,
             COALESCE(SUM(CASE WHEN module IN ('pesanan', 'pembayaran') THEN 1 ELSE 0 END), 0) AS transaksi,
             COALESCE(SUM(CASE WHEN module = 'absensi' THEN 1 ELSE 0 END), 0) AS absensi",
            [today()->toDateString()]
        )->first();

        return [
            'hari_ini'  => (int) $r->hari_ini,
            'pelanggan' => (int) $r->pelanggan,
            'transaksi' => (int) $r->transaksi,
            'absensi'   => (int) $r->absensi,
        ];
    }

    /**
     * Feed traffic / aktivitas web untuk supervisor/pemilik:
     * login pelanggan, pendaftaran, booking/pembelian, pembayaran, absensi pegawai,
     * serta aksi admin (CRUD).
     */
    public function index(Request $request)
    {
        $logs = $this->filtered($request)->latest()->latest('id')->paginate(25)->withQueryString();

        $modules = ActivityLog::query()->select('module')->distinct()->pluck('module');

        return view('backend.v_activity.index', [
            'judul'   => 'Traffic & Aktivitas',
            'logs'    => $logs,
            'modules' => $modules,
            'stats'   => $this->stats(),
            // id log terbaru yang sudah tampil; polling meminta yang lebih baru dari ini.
            'latestId' => (int) ($logs->max('id') ?? 0),
        ]);
    }

    /**
     * Polling realtime: log yang lebih baru dari ?after=ID (dengan filter yang sama).
     * Tanpa log baru hanya 1 query dan balasan kecil.
     */
    public function feed(Request $request)
    {
        $after = max(0, (int) $request->query('after', 0));

        $rows = $this->filtered($request)
            ->where('id', '>', $after)
            ->orderByDesc('id')
            ->limit(self::FEED_LIMIT)
            ->get();

        if ($rows->isEmpty()) {
            return response()->json(['count' => 0, 'latest_id' => $after]);
        }

        $html = $rows->map(fn ($log) => view('backend.v_activity._row', ['log' => $log])->render())->implode('');

        return response()->json([
            'count'     => $rows->count(),
            'latest_id' => (int) $rows->max('id'),
            'html'      => $html,
            'stats'     => $this->stats(),
        ]);
    }
}
