<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Ulasan;
use Illuminate\Http\Request;

/**
 * CRUD Ulasan layanan milik customer yang login.
 * Aturan: hanya booking layanan berstatus `done` milik sendiri yang bisa
 * diulas, satu ulasan per booking.
 */
class UlasanApiController extends Controller
{
    /** Catat ke activity_logs (user_id null: customer bukan staf; identitas di properties). */
    private function log(string $actionType, string $description, Ulasan $ulasan, array $extra = []): void
    {
        $cf = request()->header('CF-Connecting-IP');

        ActivityLog::create([
            'user_id'      => null,
            'action_type'  => $actionType,
            'module'       => 'ulasan',
            'subject_type' => Ulasan::class,
            'subject_id'   => $ulasan->id,
            'description'  => $description,
            'properties'   => array_merge([
                'customer_id'    => $ulasan->customer_id,
                'customer_nama'  => request()->user()?->nama,
                'customer_email' => request()->user()?->email,
                'order_id'       => $ulasan->order_id,
                'rating'         => $ulasan->rating,
            ], $extra),
            'ip_address'   => ($cf && filter_var($cf, FILTER_VALIDATE_IP)) ? $cf : request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);
    }

    private function format(Ulasan $u): array
    {
        return [
            'id'         => $u->id,
            'order_id'   => $u->order_id,
            'rating'     => $u->rating,
            'komentar'   => $u->komentar,
            'created_at' => $u->created_at?->toIso8601String(),
            'updated_at' => $u->updated_at?->toIso8601String(),
        ];
    }

    private function mine(Request $request, $id): ?Ulasan
    {
        return Ulasan::where('customer_id', $request->user()->id)->find($id);
    }

    private function notFound()
    {
        return response()->json(['success' => false, 'message' => 'Ulasan tidak ditemukan'], 404);
    }

    public function index(Request $request)
    {
        $data = Ulasan::where('customer_id', $request->user()->id)
            ->latest()->get()->map(fn ($u) => $this->format($u));

        return response()->json(['success' => true, 'data' => $data, 'message' => 'OK']);
    }

    public function show(Request $request, $id)
    {
        $u = $this->mine($request, $id);

        return $u
            ? response()->json(['success' => true, 'data' => $this->format($u), 'message' => 'OK'])
            : $this->notFound();
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'rating'   => 'required|integer|between:1,5',
            'komentar' => 'nullable|string|max:500',
        ]);

        $order = Order::where('customer_id', $request->user()->id)->find($request->order_id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Booking tidak ditemukan'], 404);
        }
        if ($order->jenis !== 'booking' || $order->status !== 'done') {
            return response()->json(['success' => false, 'message' => 'Hanya booking layanan yang sudah selesai yang bisa diulas'], 422);
        }
        if (Ulasan::where('order_id', $order->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Booking ini sudah diulas'], 422);
        }

        $u = Ulasan::create([
            'customer_id' => $request->user()->id,
            'order_id'    => $order->id,
            'rating'      => $request->rating,
            'komentar'    => $request->komentar,
        ]);

        $this->log('create', 'Mobile Review Created', $u);

        return response()->json(['success' => true, 'data' => $this->format($u), 'message' => 'Ulasan dikirim'], 201);
    }

    public function update(Request $request, $id)
    {
        $u = $this->mine($request, $id);
        if (!$u) {
            return $this->notFound();
        }

        $request->validate([
            'rating'   => 'required|integer|between:1,5',
            'komentar' => 'nullable|string|max:500',
        ]);

        $sebelum = ['rating' => $u->rating, 'komentar' => $u->komentar];
        $u->update($request->only('rating', 'komentar'));

        $this->log('update', 'Mobile Review Updated', $u, [
            'sebelum' => $sebelum,
            'sesudah' => ['rating' => $u->rating, 'komentar' => $u->komentar],
        ]);

        return response()->json(['success' => true, 'data' => $this->format($u), 'message' => 'Ulasan diperbarui']);
    }

    public function destroy(Request $request, $id)
    {
        $u = $this->mine($request, $id);
        if (!$u) {
            return $this->notFound();
        }

        // Catat sebelum dihapus agar snapshot rating/order masih ada.
        $this->log('delete', 'Mobile Review Deleted', $u, ['komentar' => $u->komentar]);
        $u->delete();

        return response()->json(['success' => true, 'data' => null, 'message' => 'Ulasan dihapus']);
    }
}
