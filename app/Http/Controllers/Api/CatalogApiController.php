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

    /** Key cache daftar katalog (dihapus saat data berubah). */
    private const CACHE_KEYS = ['layanan', 'produk', 'barber', 'galeri'];

    private function ok($data, string $message = 'OK')
    {
        return response()->json(['success' => true, 'data' => $data, 'message' => $message]);
    }

    /**
     * Hapus cache katalog. Dipanggil saat Layanan/Produk/Barber/Galeri/Kategori
     * berubah (lihat AppServiceProvider) agar app mobile melihat data baru.
     * Kegagalan cache tidak boleh menggagalkan penyimpanan data di panel admin.
     */
    public static function forgetCache(): void
    {
        try {
            foreach (self::CACHE_KEYS as $key) {
                Cache::store('file')->forget("catalog:{$key}");
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Daftar katalog dengan cache di store 'file' (bukan database): saat cache
     * hangat, endpoint publik tidak membuka koneksi database sama sekali.
     *
     * Cache HANYA menyimpan path gambar relatif. URL absolut dibuat per-request
     * di sini, supaya header Host / X-Forwarded-Host palsu dari satu request
     * tidak ikut tersimpan dan disajikan ke pengguna lain (cache poisoning).
     * Permintaan dengan filter pencarian tidak di-cache agar key tidak membengkak.
     */
    private function cached(Request $request, string $key, \Closure $fn)
    {
        if ($request->filled('search')) {
            $rows = $fn();
        } else {
            $store = Cache::store('file');
            $cacheKey = "catalog:{$key}";

            try {
                $rows = $store->get($cacheKey);
            } catch (\Throwable $e) {
                report($e);
                $rows = null;
            }

            if ($rows === null) {
                $rows = $fn();
                try {
                    $store->put($cacheKey, $rows, self::CACHE_TTL);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        return collect($rows)->map(
            fn ($row) => array_merge($row, ['gambar' => $this->absoluteUrl($row['gambar'])])
        );
    }

    /** Path gambar relatif (atau URL penuh bila sudah URL), tanpa host. */
    private function imgPath(?string $folder, ?string $foto): ?string
    {
        if (!$foto) return null;
        if (str_starts_with($foto, 'http')) return $foto;
        return "storage/{$folder}/{$foto}";
    }

    private function absoluteUrl(?string $path): ?string
    {
        if (!$path) return null;
        if (str_starts_with($path, 'http')) return $path;
        return asset($path);
    }

    private function img(?string $folder, ?string $foto): ?string
    {
        return $this->absoluteUrl($this->imgPath($folder, $foto));
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
                'gambar'    => $this->imgPath('img-layanan', $l->foto),
            ])->all();
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
                'gambar'    => $this->imgPath('img-produk', $p->foto),
            ])->all();
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
            'gambar'       => $this->imgPath('img-barber', $b->foto),
        ])->all());

        return $this->ok($data);
    }

    public function galeri(Request $request)
    {
        $data = $this->cached($request, 'galeri', fn () => Galeri::latest()->get()->map(fn ($g) => [
            'id'         => $g->id,
            'judul'      => $g->judul,
            'tipe'       => $g->tipe,
            'keterangan' => $g->keterangan,
            'gambar'     => $this->imgPath('img-galeri', $g->foto),
        ])->all());

        return $this->ok($data);
    }
}
