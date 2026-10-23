<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use App\Models\Produk;
use App\Models\Barber;
use App\Models\Galeri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CatalogApiController extends Controller
{
    /** Lama cache katalog publik (detik). */
    private const CACHE_TTL = 300;

    private function ok($data, string $message = 'OK')
    {
        return response()->json(['success' => true, 'data' => $data, 'message' => $message]);
    }

    /**
     * Naikkan versi cache katalog. Dipanggil saat Layanan/Produk/Barber/Galeri/
     * Kategori berubah (lihat AppServiceProvider) agar app mobile melihat data baru.
     */
    public static function bumpCacheVersion(): void
    {
        $store = Cache::store('file');
        $store->forever('catalog:version', (int) $store->get('catalog:version', 1) + 1);
    }

    /**
     * Cache hasil katalog di store 'file' (bukan database): saat cache hangat,
     * endpoint publik tidak membuka koneksi database sama sekali. Permintaan
     * dengan filter pencarian tidak di-cache agar key tidak membengkak.
     */
    private function cached(Request $request, string $key, \Closure $fn)
    {
        if ($request->filled('search')) {
            return $fn();
        }

        $store = Cache::store('file');
        $version = $store->get('catalog:version', 1);

        return $store->remember("catalog:v{$version}:{$key}", self::CACHE_TTL, $fn);
    }

    private function img(?string $folder, ?string $foto): ?string
    {
        if (!$foto) return null;
        if (str_starts_with($foto, 'http')) return $foto;
        return asset("storage/{$folder}/{$foto}");
    }

    public function layanan(Request $request)
    {
        $data = $this->cached($request, 'layanan', function () use ($request) {
            $query = Layanan::where('status', 'aktif');
            if ($request->filled('search')) {
                $query->where('nama_layanan', 'like', '%' . $request->search . '%');
            }

            return $query->orderBy('harga')->get()->map(fn ($l) => [
                'id'        => $l->id,
                'nama'      => $l->nama_layanan,
                'harga'     => (int) $l->harga,
                'durasi'    => $l->durasi_menit,
                'deskripsi' => $l->deskripsi,
                'gambar'    => $this->img('img-layanan', $l->foto),
            ]);
        });

        return $this->ok($data);
    }

    public function layananShow($id)
    {
        $l = Layanan::where('status', 'aktif')->find($id);
        if (!$l) {
            return response()->json(['success' => false, 'message' => 'Layanan tidak ditemukan'], 404);
        }

        return $this->ok([
            'id'        => $l->id,
            'nama'      => $l->nama_layanan,
            'harga'     => (int) $l->harga,
            'durasi'    => $l->durasi_menit,
            'deskripsi' => $l->deskripsi,
            'gambar'    => $this->img('img-layanan', $l->foto),
        ]);
    }

    public function produk(Request $request)
    {
        $data = $this->cached($request, 'produk', function () use ($request) {
            $query = Produk::where('status', 1);
            if ($request->filled('search')) {
                $query->where('nama_produk', 'like', '%' . $request->search . '%');
            }

            return $query->with('kategori')->latest()->get()->map(fn ($p) => [
                'id'        => $p->id,
                'nama'      => $p->nama_produk,
                'harga'     => (int) $p->harga,
                'stok'      => $p->stok,
                'berat'     => $p->berat,
                'kategori'  => $p->kategori->nama_kategori ?? '-',
                'deskripsi' => $p->detail,
                'gambar'    => $this->img('img-produk', $p->foto),
            ]);
        });

        return $this->ok($data);
    }

    public function produkShow($id)
    {
        $p = Produk::where('status', 1)->find($id);
        if (!$p) {
            return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
        }

        return $this->ok([
            'id'        => $p->id,
            'nama'      => $p->nama_produk,
            'harga'     => (int) $p->harga,
            'stok'      => $p->stok,
            'berat'     => $p->berat,
            'kategori'  => $p->kategori->nama_kategori ?? '-',
            'deskripsi' => $p->detail,
            'gambar'    => $this->img('img-produk', $p->foto),
        ]);
    }

    public function barber(Request $request)
    {
        $data = $this->cached($request, 'barber', fn () => Barber::where('status', 'aktif')->orderBy('nama')->get()->map(fn ($b) => [
            'id'           => $b->id,
            'nama'         => $b->nama,
            'spesialisasi' => $b->spesialisasi,
            'pengalaman'   => $b->pengalaman_tahun,
            'gambar'       => $this->img('img-barber', $b->foto),
        ]));

        return $this->ok($data);
    }

    public function galeri(Request $request)
    {
        $data = $this->cached($request, 'galeri', fn () => Galeri::latest()->get()->map(fn ($g) => [
            'id'         => $g->id,
            'judul'      => $g->judul,
            'tipe'       => $g->tipe,
            'keterangan' => $g->keterangan,
            'gambar'     => $this->img('img-galeri', $g->foto),
        ]));

        return $this->ok($data);
    }
}
