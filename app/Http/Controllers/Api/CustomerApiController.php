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
    private function payload(Customer $customer, string $token): array
    {
        return [
            'token'    => $token,
            'customer' => [
                'id'    => $customer->id,
                'nama'  => $customer->nama,
                'email' => $customer->email,
                'no_hp' => $customer->no_hp,
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
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $token = $customer->createToken('mobile')->plainTextToken;

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

        return response()->json([
            'success' => true,
            'data'    => $this->payload($customer, $token),
            'message' => 'Login Google berhasil',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logout berhasil']);
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
        $c->update($request->only('nama', 'no_hp', 'alamat'));

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
        ];
    }
}
