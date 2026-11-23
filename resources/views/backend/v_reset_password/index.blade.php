@extends('backend.v_layouts.app')

@section('breadcrumb')
<h4 class="page-title">Permintaan Reset Password</h4>
<div class="ml-auto">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('backend.beranda') }}">Home</a></li>
            <li class="breadcrumb-item active">Reset Password</li>
        </ol>
    </nav>
</div>
@endsection

@section('content')
@if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if (session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif

<div class="alert alert-warning">
    <i class="mdi mdi-shield-alert"></i>
    <b>Verifikasi dulu sebelum menyetujui.</b> Password sementara akan tampil di aplikasi <u>yang mengajukan permintaan</u>.
    Pastikan pemohon benar pemilik akun, misalnya hubungi nomor HP terdaftar di bawah. Menyetujui permintaan palsu
    akan mengunci pemilik asli dan memberi akses ke orang lain. Anda tidak akan melihat password sementaranya.
</div>

<div class="card">
    <div class="card-body">
        <h5 class="mb-3">Menunggu persetujuan <span class="badge badge-danger" id="pendingBadge">{{ $menunggu->count() }}</span></h5>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="thead-light">
                    <tr><th>Pelanggan</th><th>Kontak terdaftar</th><th>Diajukan</th><th>IP &amp; perangkat pemohon</th><th style="width:190px">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($menunggu as $r)
                        <tr>
                            <td><b>{{ $r->customer?->nama }}</b><br><small class="text-muted">{{ $r->customer?->email }}</small></td>
                            <td>{{ $r->customer?->no_hp ?: '— (tidak ada nomor HP)' }}</td>
                            <td><small>{{ $r->created_at->diffForHumans() }}</small><br><small class="text-muted">{{ $r->created_at->format('d/m/Y H:i') }}</small></td>
                            <td><small class="text-muted">{{ $r->ip_address }}<br>{{ \Illuminate\Support\Str::limit($r->user_agent, 60) }}</small></td>
                            <td>
                                <a href="{{ route('backend.resetpassword.show', $r) }}" class="btn btn-sm btn-outline-secondary" title="Detail"><i class="mdi mdi-text-search"></i></a>
                                <form action="{{ route('backend.resetpassword.approve', $r) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Setujui reset password untuk {{ e($r->customer?->nama) }}?\n\nPastikan pemohon sudah Anda verifikasi.')">
                                    @csrf <button class="btn btn-sm btn-success"><i class="mdi mdi-check"></i> Setujui</button>
                                </form>
                                <form action="{{ route('backend.resetpassword.reject', $r) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Tolak permintaan ini?')">
                                    @csrf <button class="btn btn-sm btn-outline-danger"><i class="mdi mdi-close"></i> Tolak</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada permintaan yang menunggu.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="mb-3">Riwayat terakhir</h5>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>Pelanggan</th><th>Status</th><th>Diajukan</th><th>Diputuskan</th><th>Oleh</th><th>Diambil app</th><th>Detail</th></tr></thead>
                <tbody>
                    @php
                        $label = ['approved' => ['Disetujui', 'success'], 'delivered' => ['Disetujui (sudah diterima app)', 'success'],
                                  'rejected' => ['Ditolak', 'danger'], 'expired' => ['Kedaluwarsa', 'secondary'], 'pending' => ['Kedaluwarsa', 'secondary']];
                    @endphp
                    @forelse ($riwayat as $r)
                        @php $l = $label[$r->status] ?? [$r->status, 'secondary']; @endphp
                        <tr>
                            <td>{{ $r->customer?->nama ?? 'Customer #'.$r->customer_id }}</td>
                            <td><span class="badge badge-{{ $l[1] }}">{{ $l[0] }}</span></td>
                            <td><small>{{ $r->created_at->format('d/m/Y H:i') }}</small></td>
                            <td><small>{{ $r->decided_at?->format('d/m/Y H:i') ?? '-' }}</small></td>
                            <td><small>{{ $r->decider?->name ?? '-' }}</small></td>
                            <td><small>{{ $r->delivered_at?->format('d/m/Y H:i') ?? '-' }}</small></td>
                            <td><a href="{{ route('backend.resetpassword.show', $r) }}" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-text-search"></i> Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Belum ada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // Muat ulang otomatis bila jumlah permintaan menunggu berubah (1 query per pemeriksaan).
    var COUNT_URL = @json(url('backend/reset-password/count'));
    var known = {{ $menunggu->count() }};
    async function cek() {
        if (document.hidden) return;
        try {
            var res = await fetch(COUNT_URL, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
            if (!res.ok || res.redirected) return;
            var d = await res.json();
            if (typeof d.pending === 'number' && d.pending !== known) { window.location.reload(); }
        } catch (e) {}
    }
    setInterval(cek, 8000);
})();
</script>
@endpush
