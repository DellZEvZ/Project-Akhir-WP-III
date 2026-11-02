<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Layanan;
use App\Models\Produk;
use App\Models\Barber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BookingApiController extends Controller
{
    /**
     * Catat aktivitas dari konteks API mobile/customer dengan aman.
     *
     * ActivityLogger::log() bawaan mengisi 'user_id' dari Auth::id() (guard
     * staff/admin) dan kolom itu punya FOREIGN KEY ke tabel `user`. Customer
     * TIDAK ada di tabel itu, jadi memanggilnya langsung dari controller ini
     * akan memicu SQLSTATE 23000 (constraint violation). Sebagai gantinya,
     * 'user_id' selalu dikosongkan (null) dan identitas customer disimpan di
     * kolom 'properties' (JSON, tanpa constraint).
     */
    private function logCustomerActivity(string $actionType, string $description, ?Order $order, array $extra = []): void
    {
        \App\Models\ActivityLog::create([
            'user_id'      => null,
            'action_type'  => $actionType,
            'module'       => 'booking',
            'subject_type' => $order ? Order::class : null,
            'subject_id'   => $order?->id,
            'description'  => $description,
            // Snapshot identitas customer: user_id dipakai admin, jadi nama customer
            // disimpan di properties agar kolom "Oleh" di panel admin bisa menampilkannya.
            'properties'   => array_merge([
                'customer_id'    => $order?->customer_id,
                'customer_nama'  => request()->user()?->nama,
                'customer_email' => request()->user()?->email,
            ], $extra),
            'ip_address'   => $this->clientIp(),
            'user_agent'   => request()->userAgent(),
        ]);
    }

    /**
     * IP asli pengguna. Di balik Cloudflare, request()->ip() berisi IP edge
     * Cloudflare; IP klien ada di CF-Connecting-IP. Hanya dipakai untuk log
     * (bukan keputusan keamanan), jadi header dipakai bila berisi IP valid.
     */
    private function clientIp(): ?string
    {
        $cf = request()->header('CF-Connecting-IP');

        return ($cf && filter_var($cf, FILTER_VALIDATE_IP)) ? $cf : request()->ip();
    }

    public function index(Request $request)
    {
        $orders = Order::with('orderItems.layanan', 'orderItems.produk', 'barber')
            ->where('customer_id', $request->user()->id)
            ->where('status', '!=', 'pending')
            ->whereNull('hidden_at')
            ->latest()
            ->get()
            ->map(fn ($o) => $this->format($o));

        return response()->json(['success' => true, 'data' => $orders]);
    }

    /**
     * Booking layanan (potong rambut, dll). Wajib pilih barber.
     * Validasi bentrok jadwal per-barber, sama seperti versi web.
     */
    public function store(Request $request)
    {
        $request->validate([
            'layanan_ids'     => 'required|array|min:1',
            'layanan_ids.*'   => 'integer|exists:layanans,id',
            'barber_id'       => 'required|integer|exists:barbers,id',
            'tanggal_booking' => 'required|date|after_or_equal:today',
            'jam_booking'     => 'required',
            'catatan'         => 'nullable|string|max:500',
        ]);

        // Cegah tabrakan jadwal: barber yang sama tidak boleh dibooking dobel
        // pada tanggal+jam yang sama. Barber lain tetap boleh jam yang sama.
        $bentrok = Order::where('jenis', 'booking')
            ->whereIn('status', ['confirmed', 'done'])
            ->where('barber_id', $request->barber_id)
            ->where('tanggal_booking', $request->tanggal_booking)
            ->where('jam_booking', $request->jam_booking)
            ->exists();

        if ($bentrok) {
            $barberNama = Barber::find($request->barber_id)->nama ?? 'Barber ini';
            return response()->json([
                'success' => false,
                'message' => "{$barberNama} sudah dibooking pada jam {$request->jam_booking} tanggal {$request->tanggal_booking}. Silakan pilih jam lain atau barber lain.",
            ], 422);
        }

        $order = Order::create([
            'customer_id'     => $request->user()->id,
            'status'          => 'confirmed',
            'jenis'           => 'booking',
            'barber_id'       => $request->barber_id,
            'tanggal_booking' => $request->tanggal_booking,
            'jam_booking'     => $request->jam_booking,
            'catatan'         => $request->catatan,
            'total_harga'     => 0,
        ]);

        $total = 0;
        foreach ($request->layanan_ids as $id) {
            $l = Layanan::find($id);
            $order->orderItems()->create([
                'layanan_id' => $l->id,
                'qty'        => 1,
                'harga'      => $l->harga,
            ]);
            $total += $l->harga;
        }
        $order->update(['total_harga' => $total]);

        $this->logCustomerActivity('create', 'Mobile Booking Created', $order);

        return response()->json([
            'success' => true,
            'data'    => $this->format($order->load('orderItems.layanan', 'barber')),
            'message' => 'Booking berhasil dibuat',
        ], 201);
    }

    /**
     * Checkout pembelian produk (dikirim, perlu alamat & ongkos kirim).
     * items: [{produk_id, qty}], ongkir dikirim terpisah (sudah dihitung
     * lewat endpoint /shipping/cost sebelumnya).
     */
    public function storeProduk(Request $request)
    {
        $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.produk_id'  => 'required|integer|exists:produk,id',
            'items.*.qty'        => 'required|integer|min:1',
            'alamat_kirim'       => 'required|string|max:500',
            'kota_tujuan_id'     => 'required|string',
            'kota_tujuan_label'  => 'required|string',
            'kurir'              => 'required|string',
            'layanan_ongkir'     => 'required|string',
            'biaya_ongkir'       => 'required|numeric|min:0',
            'estimasi_ongkir'    => 'nullable|string',
            'total_berat'        => 'required|numeric|min:1',
            'catatan'            => 'nullable|string|max:500',
        ]);

        $order = Order::create([
            'customer_id'       => $request->user()->id,
            'status'            => 'confirmed',
            'jenis'             => 'produk',
            'alamat_kirim'      => $request->alamat_kirim,
            'kota_tujuan_id'    => $request->kota_tujuan_id,
            'kota_tujuan_label' => $request->kota_tujuan_label,
            'kurir'             => strtoupper($request->kurir),
            'layanan_ongkir'    => $request->layanan_ongkir,
            'biaya_ongkir'      => $request->biaya_ongkir,
            'estimasi_ongkir'   => $request->estimasi_ongkir,
            'total_berat'       => $request->total_berat,
            'catatan'           => $request->catatan,
            'total_harga'       => 0,
        ]);

        $total = 0;
        foreach ($request->items as $item) {
            $p = Produk::find($item['produk_id']);
            if (!$p) continue;

            $order->orderItems()->create([
                'produk_id' => $p->id,
                'qty'       => $item['qty'],
                'harga'     => $p->harga,
            ]);
            $total += $p->harga * $item['qty'];
        }
        $order->update(['total_harga' => $total]);

        $this->logCustomerActivity('create', 'Mobile Product Order Created', $order);

        return response()->json([
            'success' => true,
            'data'    => $this->format($order->load('orderItems.produk')),
            'message' => 'Pesanan produk berhasil dibuat',
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $order = Order::with('orderItems.layanan', 'orderItems.produk', 'barber')
            ->where('customer_id', $request->user()->id)
            ->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Booking tidak ditemukan'], 404);
        }

        return response()->json(['success' => true, 'data' => $this->format($order)]);
    }

    public function pay(Request $request, $id)
    {
        $request->validate([
            'metode_bayar' => 'required|in:transfer,cash,ewallet',
            'kanal_bayar'  => 'nullable|string|max:50',
        ]);

        $order = Order::where('customer_id', $request->user()->id)->find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Booking tidak ditemukan'], 404);
        }

        if ($request->metode_bayar === 'cash') {
            $order->update([
                'metode_bayar' => 'cash',
                'kanal_bayar'  => 'Bayar di Tempat',
                'status_bayar' => 'belum',
            ]);
        } else {
            $order->update([
                'metode_bayar' => $request->metode_bayar,
                'kanal_bayar'  => $request->kanal_bayar,
                'status_bayar' => 'lunas',
                'no_ref'       => 'BF-' . now()->format('ymdHis') . '-' . strtoupper(\Illuminate\Support\Str::random(4)),
                'dibayar_pada' => now(),
            ]);
        }

        $this->logCustomerActivity(
            'update',
            "Mobile Payment Processed via {$order->metode_bayar}",
            $order,
            ['metode_bayar' => $order->metode_bayar, 'no_ref' => $order->no_ref]
        );

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                 => $order->id,
                'metode_bayar'       => $order->metode_bayar,
                'kanal_bayar'        => $order->kanal_bayar,
                'no_ref'             => $order->no_ref,
                'total_harga'        => (int) $order->total_harga,
                'biaya_ongkir'       => (int) $order->biaya_ongkir,
                'total_akhir'        => (int) $order->total_akhir,
                'status_bayar'       => $order->status_bayar,
                'status_bayar_label' => $order->status_bayar_label,
            ],
            'message' => 'Pembayaran tercatat',
        ]);
    }

    /**
     * Proxy pencarian tujuan RajaOngkir (sama seperti web).
     */
    public function searchShipping(Request $request)
    {
        $keyword = $request->input('search');
        if (!$keyword || strlen($keyword) < 3) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $response = Http::timeout(10)->connectTimeout(5)->withHeaders([
            'key' => config('services.rajaongkir.api_key'),
        ])->get(config('services.rajaongkir.base_url') . '/destination/domestic-destination', [
            'search' => $keyword,
            'limit'  => 20,
            'offset' => 0,
        ]);

        $body = $response->json();

        return response()->json(['success' => true, 'data' => $body['data'] ?? []]);
    }

    /**
     * Proxy hitung ongkos kirim RajaOngkir (sama seperti web).
     */
    public function shippingCost(Request $request)
    {
        $request->validate([
            'destination' => 'required',
            'weight'      => 'required|numeric|min:1',
            'courier'     => 'required|string',
        ]);

        $response = Http::asForm()->timeout(10)->connectTimeout(5)->withHeaders([
            'key' => config('services.rajaongkir.api_key'),
        ])->post(config('services.rajaongkir.base_url') . '/calculate/domestic-cost', [
            'origin'      => config('services.rajaongkir.origin'),
            'destination' => $request->input('destination'),
            'weight'      => $request->input('weight'),
            'courier'     => $request->input('courier'),
        ]);

        $body = $response->json();

        return response()->json(['success' => true, 'data' => $body['data'] ?? []]);
    }

    private function format(Order $o): array
    {
        return [
            'id'                 => $o->id,
            'jenis'              => $o->jenis,
            'status'             => $o->status,
            'status_label'       => $o->status_label,
            'status_bayar'       => $o->status_bayar,
            'status_bayar_label' => $o->status_bayar_label,
            'metode_bayar'       => $o->metode_bayar,
            'kanal_bayar'        => $o->kanal_bayar,
            'no_ref'             => $o->no_ref,
            'dibayar_pada'       => $o->dibayar_pada?->format('d M Y H:i'),
            'tanggal_booking'    => $o->tanggal_booking?->toDateString(),
            'jam_booking'        => $o->jam_booking ? \Carbon\Carbon::parse($o->jam_booking)->format('H:i') : null,
            'barber'             => $o->barber ? ['id' => $o->barber->id, 'nama' => $o->barber->nama] : null,
            'alamat_kirim'       => $o->alamat_kirim,
            'kota_tujuan_label'  => $o->kota_tujuan_label,
            'kurir'              => $o->kurir,
            'layanan_ongkir'     => $o->layanan_ongkir,
            'estimasi_ongkir'    => $o->estimasi_ongkir,
            'biaya_ongkir'       => (int) $o->biaya_ongkir,
            'total_harga'        => (int) $o->total_harga,
            'total_akhir'        => (int) $o->total_akhir,
            'catatan'            => $o->catatan,
            'items'              => $o->orderItems->map(fn ($i) => [
                'nama'  => $i->produk_id ? ($i->produk->nama_produk ?? 'Produk') : ($i->layanan->nama_layanan ?? 'Layanan'),
                'tipe'  => $i->produk_id ? 'produk' : 'layanan',
                'qty'   => $i->qty,
                'harga' => (int) $i->harga,
            ]),
        ];
    }
}
