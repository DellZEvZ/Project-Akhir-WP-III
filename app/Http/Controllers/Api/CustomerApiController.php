<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class CustomerApiController extends Controller
{
    /**
     * Catat aktivitas customer (register/login/profil/hapus akun) ke activity_logs.
     * user_id selalu null (customer bukan staf; FK ke tabel user); identitas ada
     * di properties. Kegagalan log TIDAK boleh menggagalkan request utamanya.
     */
    private function log(string $actionType, string $description, ?Customer $c, array $extra = []): void
    {
        try {
            $cf = request()->header('CF-Connecting-IP');

            \App\Models\ActivityLog::create([
                'user_id'      => null,
                'action_type'  => $actionType,
                'module'       => 'pelanggan',
                'subject_type' => $c ? Customer::class : null,
                'subject_id'   => $c?->id,
                'description'  => $description,
                'properties'   => array_merge([
                    'customer_id'    => $c?->id,
                    'customer_nama'  => $c?->nama,
                    'customer_email' => $c?->email,
                ], $extra),
                'ip_address'   => ($cf && filter_var($cf, FILTER_VALIDATE_IP)) ? $cf : request()->ip(),
                'user_agent'   => request()->userAgent(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function payload(Customer $customer, string $token): array
    {
        return [
            'token'    => $token,
            'customer' => [
                'id'    => $customer->id,
                'nama'  => $customer->nama,
                'email' => $customer->email,
                'no_hp' => $customer->no_hp,
                'must_change_password' => (bool) $customer->must_change_password,
            ],
        ];
    }

    public function register(Request $request)
    {
        $request->validate([
            'nama'     => 'required|string|max:255',
            'email'    => 'required|email|unique:customers,email',
            'password' => 'required|min:6',
        ]);

        $customer = Customer::create([
            'nama'     => $request->nama,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $customer->createToken('mobile')->plainTextToken;
        $this->log('create', 'Mobile Register', $customer);

        return response()->json([
            'success' => true,
            'data'    => $this->payload($customer, $token),
            'message' => 'Registrasi berhasil',
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer || !$customer->password || !Hash::check($request->password, $customer->password)) {
            // Email yang diketik dicatat (bukan password) untuk mendeteksi percobaan berulang.
            $this->log('login', 'Mobile Login Failed', $customer, ['customer_email' => $request->email]);
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if ($customer->must_change_password && $customer->password_temp_expires_at?->isPast()) {
            throw ValidationException::withMessages([
                'email' => ['Password sementara sudah kedaluwarsa. Ajukan reset password lagi.'],
            ]);
        }

        $token = $customer->createToken('mobile')->plainTextToken;
        $this->log('login', 'Mobile Login', $customer);

        return response()->json([
            'success' => true,
            'data'    => $this->payload($customer, $token),
            'message' => 'Login berhasil',
        ]);
    }

    /**
     * Login/registrasi lewat Google untuk app mobile.
     *
     * App mengirim ID token dari Google Sign-In native. Token diverifikasi ke
     * Google (tokeninfo), lalu audience-nya HARUS salah satu client ID kita,
     * supaya token milik aplikasi lain tidak bisa dipakai masuk ke sini.
     */
    public function google(Request $request)
    {
        $request->validate(['id_token' => 'required|string']);

        $res = Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $request->id_token,
        ]);

        $info = $res->json();
        $allowedAud = array_filter([
            config('services.google.client_id'),
            env('GOOGLE_ANDROID_CLIENT_ID'),
        ]);

        if (
            !$res->ok()
            || !in_array($info['aud'] ?? null, $allowedAud, true)
            || !in_array($info['iss'] ?? null, ['accounts.google.com', 'https://accounts.google.com'], true)
            || ($info['email_verified'] ?? 'false') !== 'true'
            || empty($info['sub']) || empty($info['email'])
        ) {
            throw ValidationException::withMessages([
                'id_token' => ['Token Google tidak valid.'],
            ]);
        }

        // Cocokkan google_id, lalu email (menautkan akun yang dibuat lewat
        // email/password), baru buat akun baru.
        $customer = Customer::where('google_id', $info['sub'])->first()
            ?? Customer::where('email', $info['email'])->first();

        if ($customer) {
            $customer->google_id = $customer->google_id ?: $info['sub'];
            if (!$customer->foto && !empty($info['picture'])) {
                $customer->foto = $info['picture'];
            }
            $customer->save();
        } else {
            $customer = Customer::create([
                'nama'      => $info['name'] ?? $info['email'],
                'email'     => $info['email'],
                'google_id' => $info['sub'],
                'foto'      => $info['picture'] ?? null,
            ]);
        }

        $token = $customer->createToken('mobile')->plainTextToken;
        $this->log('login', 'Mobile Login Google', $customer);

        return response()->json([
            'success' => true,
            'data'    => $this->payload($customer, $token),
            'message' => 'Login Google berhasil',
        ]);
    }

    private const RESET_MASA_BERLAKU_JAM = 24;
    private const RESET_MAX_PENDING = 3;

    /**
     * Lupa password, langkah 1: pengguna hanya mengisi email. Server membuat PERMINTAAN
     * yang harus disetujui admin di panel, dan membalas `request_token` yang hanya dipegang
     * app pemohon; token itu satu-satunya cara mengambil password sementara setelah disetujui.
     * Email yang tidak terdaftar langsung ditolak dengan pesan jelas (keberadaan email memang
     * sudah bisa diketahui lewat registrasi, jadi menyembunyikannya di sini tidak melindungi apa pun).
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer || str_ends_with($customer->email, '@deleted.invalid')) {
            $this->log('update', 'Mobile Password Reset Requested (email tidak terdaftar)', null, [
                'customer_email' => $request->email, 'terdaftar' => false,
            ]);
            throw ValidationException::withMessages(['email' => ['Email tidak terdaftar.']]);
        }

        if (\App\Models\PasswordResetRequest::where('customer_id', $customer->id)->aktif()->count() >= self::RESET_MAX_PENDING) {
            throw ValidationException::withMessages([
                'email' => ['Sudah ada permintaan yang menunggu persetujuan admin untuk akun ini. Mohon tunggu.'],
            ]);
        }

        $token = \Illuminate\Support\Str::random(48);
        $cf = $request->header('CF-Connecting-IP');
        \App\Models\PasswordResetRequest::create([
            'customer_id' => $customer->id,
            'token_hash'  => hash('sha256', $token),
            'expires_at'  => now()->addHours(self::RESET_MASA_BERLAKU_JAM),
            'ip_address'  => ($cf && filter_var($cf, FILTER_VALIDATE_IP)) ? $cf : $request->ip(),
            'user_agent'  => substr((string) $request->userAgent(), 0, 255),
        ]);
        $this->log('update', 'Mobile Password Reset Requested', $customer, ['terdaftar' => true]);

        return response()->json([
            'success' => true,
            'data'    => ['request_token' => $token],
            'message' => 'Permintaan dikirim. Menunggu persetujuan admin.',
        ]);
    }

    /**
     * Lupa password, langkah 2: app menanyakan status permintaannya memakai request_token.
     * Saat admin sudah menyetujui, password sementara diserahkan SEKALI saja.
     */
    public function passwordStatus(Request $request)
    {
        $request->validate(['request_token' => 'required|string|max:100']);

        $row = \App\Models\PasswordResetRequest::with('customer')
            ->where('token_hash', hash('sha256', $request->request_token))->first();

        // Token tak dikenal dijawab "pending" agar tidak bisa dipakai menebak apa pun.
        if (!$row) {
            return response()->json(['success' => true, 'data' => ['status' => 'pending'], 'message' => 'OK']);
        }

        if ($row->status === 'pending' && $row->expires_at->isPast()) {
            $row->update(['status' => 'expired']);
            $row->status = 'expired';
        }

        if ($row->status === 'approved') {
            $temp = null;
            try {
                $temp = \Illuminate\Support\Facades\Crypt::decryptString($row->temp_password_enc);
            } catch (\Throwable $e) {
                report($e);
            }
            // Pembaruan bersyarat: hanya satu pemanggilan yang berhasil mengambil password.
            $cf = $request->header('CF-Connecting-IP');
            $diambil = \App\Models\PasswordResetRequest::where('id', $row->id)->where('status', 'approved')
                ->update([
                    'status'                => 'delivered',
                    'temp_password_enc'     => null,
                    'delivered_at'          => now(),
                    'delivered_ip'          => ($cf && filter_var($cf, FILTER_VALIDATE_IP)) ? $cf : $request->ip(),
                    'delivered_user_agent'  => substr((string) $request->userAgent(), 0, 255),
                ]);

            if ($diambil && $temp) {
                $this->log('update', 'Mobile Password Reset Delivered', $row->customer);
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'status'         => 'approved',
                        'temp_password'  => $temp,
                        'berlaku_hingga' => $row->customer->password_temp_expires_at?->toIso8601String(),
                    ],
                    'message' => 'Password direset oleh admin.',
                ]);
            }
            $row->status = 'delivered';
        }

        return response()->json(['success' => true, 'data' => ['status' => $row->status], 'message' => 'OK']);
    }

    /** Ganti password dari profil (juga dipakai untuk mengganti password sementara). */
    public function changePassword(Request $request)
    {
        $c = $request->user();

        $request->validate([
            'current_password' => $c->password ? 'required|string' : 'nullable',
            'password'         => 'required|string|min:6',
        ]);

        if ($c->password && !Hash::check($request->current_password, $c->password)) {
            throw ValidationException::withMessages(['current_password' => ['Password saat ini salah.']]);
        }
        if ($c->password && Hash::check($request->password, $c->password)) {
            throw ValidationException::withMessages(['password' => ['Password baru tidak boleh sama dengan yang lama.']]);
        }

        $c->forceFill([
            'password'                 => Hash::make($request->password),
            'must_change_password'     => false,
            'password_temp_expires_at' => null,
        ])->save();

        // Perangkat lain harus login ulang; sesi yang sedang dipakai tetap berjalan.
        $c->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();
        $this->log('update', 'Mobile Password Changed', $c);

        return response()->json(['success' => true, 'data' => $this->profile($c->fresh()), 'message' => 'Password berhasil diubah']);
    }

    public function logout(Request $request)
    {
        $this->log('logout', 'Mobile Logout', $request->user());
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logout berhasil']);
    }

    /**
     * Hapus akun = anonimisasi (bukan hapus baris): order lama masih
     * mereferensikan customer, jadi riwayat di panel admin tetap utuh.
     * Akun berpassword wajib konfirmasi password; akun Google tidak punya password.
     */
    public function destroy(Request $request)
    {
        $c = $request->user();

        if ($c->password) {
            $request->validate(['password' => 'required|string']);
            if (!Hash::check($request->password, $c->password)) {
                throw ValidationException::withMessages(['password' => ['Password salah.']]);
            }
        }

        // Komentar ulasan bisa memuat data pribadi, jadi ikut dihapus (order tetap ada).
        $ulasanDihapus = \App\Models\Ulasan::where('customer_id', $c->id)->delete();

        // Catat sebelum data diubah; email sengaja tidak disimpan di log penghapusan.
        $this->log('delete', 'Mobile Account Deleted', $c, [
            'customer_email' => null,
            'ulasan_dihapus' => $ulasanDihapus,
        ]);

        $c->tokens()->delete();
        $c->forceFill([
            'nama'      => 'Pengguna Terhapus',
            'email'     => "terhapus-{$c->id}@deleted.invalid",
            'password'  => null,
            'no_hp'     => null,
            'alamat'    => null,
            'foto'      => null,
            'google_id' => null,
        ])->save();

        return response()->json(['success' => true, 'data' => null, 'message' => 'Akun dihapus']);
    }

    public function me(Request $request)
    {
        return response()->json(['success' => true, 'data' => $this->profile($request->user())]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'nama'   => 'required|string|max:255',
            'no_hp'  => 'nullable|string|max:30',
            'alamat' => 'nullable|string|max:500',
        ]);

        $c = $request->user();
        $sebelum = $c->only('nama', 'no_hp', 'alamat');
        $c->update($request->only('nama', 'no_hp', 'alamat'));
        $this->log('update', 'Mobile Profile Updated', $c, [
            'sebelum' => $sebelum,
            'sesudah' => $c->only('nama', 'no_hp', 'alamat'),
        ]);

        return response()->json([
            'success' => true,
            'data'    => $this->profile($c->fresh()),
            'message' => 'Profil diperbarui',
        ]);
    }

    private function profile(Customer $c): array
    {
        return [
            'id'     => $c->id,
            'nama'   => $c->nama,
            'email'  => $c->email,
            'no_hp'  => $c->no_hp,
            'alamat' => $c->alamat,
            'must_change_password' => (bool) $c->must_change_password,
        ];
    }
}
