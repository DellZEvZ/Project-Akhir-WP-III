<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Customer yang baru direset admin memakai password sementara: sampai password itu
 * diganti, hanya profil (GET /me), ganti password, dan logout yang boleh dipakai.
 * Ini ditegakkan di server, bukan hanya di layar app.
 */
class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            $boleh = ($request->isMethod('GET') && $request->is('api/me'))
                || ($request->isMethod('POST') && $request->is('api/me/password', 'api/logout'));

            if (!$boleh) {
                return response()->json([
                    'success' => false,
                    'code'    => 'password_change_required',
                    'message' => 'Anda harus mengganti password sementara terlebih dahulu.',
                ], 403);
            }
        }

        return $next($request);
    }
}
