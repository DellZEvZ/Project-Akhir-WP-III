@extends('backend.v_layouts.app')

@section('breadcrumb')
<h4 class="page-title">Detail Permintaan Reset Password</h4>
<div class="ml-auto">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('backend.beranda') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('backend.resetpassword.index') }}">Reset Password</a></li>
            <li class="breadcrumb-item active">#{{ $r->id }}</li>
        </ol>
    </nav>
</div>
@endsection

@section('content')
@php
    $status = [
        'pending'   => $r->expires_at->isPast() ? ['Kedaluwarsa (tidak diputuskan)', 'secondary'] : ['Menunggu persetujuan', 'warning'],
        'approved'  => ['Disetujui (belum diambil app)', 'success'],
        'delivered' => ['Disetujui (password sudah diambil app)', 'success'],
        'rejected'  => ['Ditolak', 'danger'],
        'expired'   => ['Kedaluwarsa', 'secondary'],
    ][$r->status] ?? [$r->status, 'secondary'];
    $fmt = fn ($t) => $t ? $t->format('d/m/Y H:i:s') : '-';
@endphp

<a href="{{ route('backend.resetpassword.index') }}" class="btn btn-sm btn-outline-secondary mb-3"><i class="mdi mdi-arrow-left"></i> Kembali</a>

<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="card"><div class="card-body">
            <h5 class="mb-3">Ringkasan <span class="badge badge-{{ $status[1] }}">{{ $status[0] }}</span></h5>
            <table class="table table-sm table-borderless mb-0">
                <tr><th style="width:42%">ID permintaan</th><td>#{{ $r->id }}</td></tr>
                <tr><th>Pelanggan</th><td>{{ $r->customer?->nama ?? 'Customer #'.$r->customer_id }}<br><small class="text-muted">{{ $r->customer?->email }}</small></td></tr>
                <tr><th>Nomor HP terdaftar</th><td>{{ $r->customer?->no_hp ?: '-' }}</td></tr>
                <tr><th colspan="2" class="pt-3 text-muted">Pengajuan</th></tr>
                <tr><th>Diajukan</th><td>{{ $fmt($r->created_at) }}</td></tr>
                <tr><th>IP pemohon</th><td>{{ $r->ip_address ?: '-' }}</td></tr>
                <tr><th>Perangkat pemohon</th><td><small>{{ $r->user_agent ?: '-' }}</small></td></tr>
                <tr><th>Batas berlaku</th><td>{{ $fmt($r->expires_at) }}</td></tr>
                <tr><th colspan="2" class="pt-3 text-muted">Keputusan admin</th></tr>
                <tr><th>Diputuskan oleh</th><td>{{ $r->decider ? $r->decider->name . ' (' . $r->decider->email . ')' : '-' }}</td></tr>
                <tr><th>Waktu keputusan</th><td>{{ $fmt($r->decided_at) }}</td></tr>
                <tr><th colspan="2" class="pt-3 text-muted">Penyerahan password sementara ke app</th></tr>
                <tr><th>Diambil pada</th><td>{{ $fmt($r->delivered_at) }}</td></tr>
                <tr><th>IP pengambil</th><td>{{ $r->delivered_ip ?: '-' }}
                    @if ($r->delivered_ip && $r->ip_address && $r->delivered_ip !== $r->ip_address)
                        <span class="badge badge-warning" title="IP saat mengajukan berbeda dari IP saat mengambil password">IP berbeda dari pengajuan</span>
                    @endif
                </td></tr>
                <tr><th>Perangkat pengambil</th><td><small>{{ $r->delivered_user_agent ?: '-' }}</small></td></tr>
            </table>
        </div></div>
    </div>

    <div class="col-lg-6 mb-3">
        <div class="card"><div class="card-body">
            <h5 class="mb-3">Kesimpulan penelusuran</h5>
            @if ($r->decided_at && $r->status !== 'rejected')
                <ul class="mb-0">
                    <li>Password sementara <b>{{ $kesimpulan['diganti_pemohon'] ? 'sudah diganti oleh pemohon' : 'belum diganti pemohon' }}</b>.
                        @if ($kesimpulan['jendela_berakhir'])
                            <small class="text-muted">(dinilai sampai {{ $kesimpulan['jendela_berakhir']->format('d/m/Y H:i') }}, saat permintaan lain pada akun ini diputuskan)</small>
                        @endif
                    </li>
                    <li>Login berhasil sesudah disetujui: <b>{{ $kesimpulan['login_setelah'] }}</b> kali; login gagal: <b>{{ $kesimpulan['login_gagal'] }}</b> kali.</li>
                    <li>Password {{ $r->delivered_at ? 'sudah diambil app pada ' . $fmt($r->delivered_at) : 'belum diambil app' }}.</li>
                </ul>
                <hr>
                <small class="text-muted">
                    Bila persetujuan ini keliru (pemohon bukan pemilik akun): pemilik asli perlu mengajukan reset lagi dan
                    diverifikasi. Gunakan lini masa di bawah untuk melihat apa yang dilakukan pada akun ini sejak permintaan.
                </small>
            @elseif ($r->status === 'rejected')
                <p class="mb-0">Permintaan ini ditolak; password akun tidak diubah.</p>
            @else
                <p class="mb-0">Belum ada keputusan admin; password akun belum diubah.</p>
            @endif
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="mb-3">Lini masa akun sejak permintaan ini</h5>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="thead-light"><tr><th style="width:150px">Waktu</th><th>Kejadian</th><th>Oleh</th><th>IP</th></tr></thead>
                <tbody>
                    @forelse ($lini as $l)
                        @php $penting = str_contains($l->description, 'Password') || str_starts_with($l->description, 'Admin'); @endphp
                        <tr @if($penting) class="table-warning" @endif>
                            <td><small>{{ $l->created_at->format('d/m/Y H:i:s') }}</small></td>
                            <td>{{ $l->description }}</td>
                            <td>
                                @if ($l->user) {{ $l->user->name }} <span class="badge badge-light">Admin</span>
                                @else <span class="text-muted">Pelanggan</span> @endif
                            </td>
                            <td><small class="text-muted">{{ $l->ip_address }}</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Belum ada kejadian tercatat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <small class="text-muted">Baris kuning = kejadian terkait password. Password sementara tidak pernah dicatat.</small>
    </div>
</div>
@endsection
