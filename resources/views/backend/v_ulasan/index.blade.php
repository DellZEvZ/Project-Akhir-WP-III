@extends('backend.v_layouts.app')

@section('breadcrumb')
<h4 class="page-title">Ulasan Pelanggan</h4>
<div class="ml-auto">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('backend.beranda') }}">Home</a></li>
            <li class="breadcrumb-item active">Ulasan</li>
        </ol>
    </nav>
</div>
@endsection

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row">
    <div class="col-md-3 mb-3"><div class="card text-white" style="background:linear-gradient(135deg,#800020,#a0283c)"><div class="card-body"><h6 class="mb-1 text-white-50">Total Ulasan</h6><h3 class="mb-0">{{ $total }}</h3></div></div></div>
    <div class="col-md-3 mb-3"><div class="card text-white" style="background:linear-gradient(135deg,#c9821f,#e0a040)"><div class="card-body"><h6 class="mb-1 text-white-50">Rata-rata Rating</h6><h3 class="mb-0">{{ $rata }} <small>/ 5</small></h3></div></div></div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('backend.ulasan.index') }}" class="row mb-3">
            <div class="col-md-3">
                <select name="rating" class="form-control" onchange="this.form.submit()">
                    <option value="">Semua Rating</option>
                    @for ($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} bintang</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-5">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari komentar atau nama pelanggan">
            </div>
            <div class="col-md-2"><button class="btn btn-secondary btn-block">Cari</button></div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr><th>Pelanggan</th><th>Layanan</th><th>Rating</th><th>Komentar</th><th>Waktu</th><th style="width:80px">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($ulasans as $u)
                        <tr>
                            <td>{{ $u->customer?->nama ?? 'Customer #'.$u->customer_id }}</td>
                            <td><small>{{ $u->order?->orderItems->pluck('layanan.nama')->filter()->implode(', ') ?: '-' }}</small></td>
                            <td><span style="color:#e0a040;letter-spacing:1px">{{ str_repeat('★', $u->rating) }}{{ str_repeat('☆', 5 - $u->rating) }}</span></td>
                            <td>{{ $u->komentar ?: '-' }}</td>
                            <td><small>{{ $u->created_at->format('d/m/Y H:i') }}</small></td>
                            <td>
                                <form action="{{ route('backend.ulasan.destroy', $u) }}" method="POST" onsubmit="return confirm('Hapus ulasan ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="mdi mdi-delete"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">Belum ada ulasan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $ulasans->links() }}</div>
    </div>
</div>
@endsection
