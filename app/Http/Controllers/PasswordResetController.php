<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\PasswordResetRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Persetujuan reset password customer dari app mobile.
 * Admin TIDAK pernah melihat password sementara: ia dibuat di sini, disimpan
 * terenkripsi, lalu hanya diserahkan ke app pemohon (sekali) lewat request_token-nya.
 */
class PasswordResetController extends Controller
{
    private const MASA_BERLAKU_JAM = 24;
    private const HURUF = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'; // tanpa 0/O/1/l/I

    private function otorisasi(): void
    {
        $u = auth()->user();
        abort_unless($u && ($u->hasRole('super-admin') || $u->hasRole('admin')), 403, 'Hanya Super Admin atau Admin.');
    }

    private function passwordSementara(int $panjang = 10): string
    {
        $out = '';
        $maks = strlen(self::HURUF) - 1;
        for ($i = 0; $i < $panjang; $i++) {
            $out .= self::HURUF[random_int(0, $maks)];
        }
        return $out;
    }

    public function index()
    {
        $this->otorisasi();

        return view('backend.v_reset_password.index', [
            'judul'    => 'Permintaan Reset Password',
            'menunggu' => PasswordResetRequest::with('customer')->aktif()->latest()->get(),
            'riwayat'  => PasswordResetRequest::with(['customer', 'decider'])
                ->where(fn ($q) => $q->where('status', '!=', 'pending')->orWhere('expires_at', '<=', now()))
                ->latest()->limit(20)->get(),
        ]);
    }

    /**
     * Detail satu permintaan untuk penelusuran: siapa memutuskan dan kapan, dari mana
     * permintaan diajukan dan password diambil, serta lini masa aksi akun itu sesudahnya.
     */
    public function show(PasswordResetRequest $permintaan)
    {
        $this->otorisasi();
        $permintaan->load(['customer', 'decider']);
        $customer = $permintaan->customer;

        $lini = collect();
        if ($customer) {
            $lini = \App\Models\ActivityLog::with('user')
                ->where('subject_type', \App\Models\Customer::class)
                ->where('subject_id', $customer->id)
                ->where('created_at', '>=', $permintaan->created_at->copy()->subSeconds(5))
                ->orderBy('created_at')->orderBy('id')
                ->limit(200)->get();
        }

        // Kesimpulan hanya menilai jendela waktu permintaan INI: dari keputusannya sampai keputusan
        // berikutnya pada akun yang sama (agar permintaan lama tidak ikut terpengaruh permintaan baru).
        $mulai = $permintaan->decided_at;
        $akhir = $mulai
            ? PasswordResetRequest::where('customer_id', $permintaan->customer_id)
                ->where('id', '!=', $permintaan->id)->where('decided_at', '>', $mulai)->min('decided_at')
            : null;
        $akhir = $akhir ? \Illuminate\Support\Carbon::parse($akhir) : null;
        $setelah = $mulai
            ? $lini->filter(fn ($l) => $l->created_at->gte($mulai) && (!$akhir || $l->created_at->lt($akhir)))
            : collect();
        $kesimpulan = [
            'diganti_pemohon' => $setelah->contains(fn ($l) => $l->description === 'Mobile Password Changed'),
            'login_setelah'   => $setelah->filter(fn ($l) => $l->description === 'Mobile Login')->count(),
            'login_gagal'     => $setelah->filter(fn ($l) => str_starts_with($l->description, 'Mobile Login Failed'))->count(),
            'jendela_berakhir' => $akhir,
        ];

        return view('backend.v_reset_password.show', [
            'judul'      => 'Detail Permintaan Reset Password',
            'r'          => $permintaan,
            'lini'       => $lini,
            'kesimpulan' => $kesimpulan,
        ]);
    }

    /** Dipolling halaman admin; 1 query. Halaman dimuat ulang bila jumlahnya berubah. */
    public function count()
    {
        $this->otorisasi();

        return response()->json(['pending' => PasswordResetRequest::aktif()->count()]);
    }

    public function approve(PasswordResetRequest $permintaan)
    {
        $this->otorisasi();

        if ($permintaan->status !== 'pending' || $permintaan->expires_at->isPast()) {
            return back()->with('error', 'Permintaan ini sudah diproses atau kedaluwarsa.');
        }

        $customer = $permintaan->customer;
        $temp = $this->passwordSementara();

        DB::transaction(function () use ($permintaan, $customer, $temp) {
            $customer->forceFill([
                'password'                 => Hash::make($temp),
                'must_change_password'     => true,
                'password_temp_expires_at' => now()->addHours(self::MASA_BERLAKU_JAM),
            ])->save();
            $customer->tokens()->delete(); // semua sesi lama keluar

            $permintaan->update([
                'status'            => 'approved',
                'temp_password_enc' => Crypt::encryptString($temp),
                'decided_by'        => auth()->id(),
                'decided_at'        => now(),
            ]);

            // Permintaan lain milik customer yang sama tidak lagi relevan.
            PasswordResetRequest::where('customer_id', $customer->id)->where('id', '!=', $permintaan->id)
                ->where('status', 'pending')
                ->update(['status' => 'rejected', 'decided_by' => auth()->id(), 'decided_at' => now()]);
        });

        // Password sementara sengaja TIDAK masuk log.
        ActivityLogger::log('update', 'pelanggan', "Admin menyetujui reset password: {$customer->nama}", $customer, [
            'customer_id' => $customer->id, 'request_id' => $permintaan->id,
        ]);

        return redirect()->route('backend.resetpassword.index')
            ->with('success', "Reset untuk {$customer->nama} disetujui. Password sementara akan tampil di aplikasi pemohon.");
    }

    public function reject(PasswordResetRequest $permintaan)
    {
        $this->otorisasi();

        if ($permintaan->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        $permintaan->update(['status' => 'rejected', 'decided_by' => auth()->id(), 'decided_at' => now()]);
        ActivityLogger::log('update', 'pelanggan', 'Admin menolak reset password: ' . ($permintaan->customer->nama ?? '-'), $permintaan->customer, [
            'customer_id' => $permintaan->customer_id, 'request_id' => $permintaan->id,
        ]);

        return redirect()->route('backend.resetpassword.index')->with('success', 'Permintaan ditolak.');
    }
}
