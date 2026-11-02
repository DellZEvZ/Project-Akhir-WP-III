@extends('backend.v_layouts.app')

@section('breadcrumb')
<h4 class="page-title">Traffic &amp; Aktivitas</h4>
<div class="ml-auto">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('backend.beranda') }}">Home</a></li>
            <li class="breadcrumb-item active">Traffic &amp; Aktivitas</li>
        </ol>
    </nav>
</div>
@endsection

@section('content')
@php
    $icons = [
        'login' => ['mdi-login', '#2e9bbd'], 'logout' => ['mdi-logout', '#888'],
        'create' => ['mdi-plus-circle', '#27ae60'], 'update' => ['mdi-pencil', '#c9821f'],
        'delete' => ['mdi-delete', '#c0392b'], 'view' => ['mdi-eye', '#666'], 'export' => ['mdi-download', '#555'],
    ];
@endphp

<div class="row">
    <div class="col-md-3 mb-3"><div class="card text-white" style="background:linear-gradient(135deg,#800020,#a0283c)"><div class="card-body d-flex justify-content-between align-items-center"><div><h6 class="mb-1 text-white-50">Aktivitas Hari Ini</h6><h3 class="mb-0">{{ $stats['hari_ini'] }}</h3></div><i class="mdi mdi-pulse" style="font-size:38px;opacity:.4"></i></div></div></div>
    <div class="col-md-3 mb-3"><div class="card text-white" style="background:linear-gradient(135deg,#1f6f8b,#2e9bbd)"><div class="card-body d-flex justify-content-between align-items-center"><div><h6 class="mb-1 text-white-50">Aktivitas Pelanggan</h6><h3 class="mb-0">{{ $stats['pelanggan'] }}</h3></div><i class="mdi mdi-account-group" style="font-size:38px;opacity:.4"></i></div></div></div>
    <div class="col-md-3 mb-3"><div class="card text-white" style="background:linear-gradient(135deg,#1a6b3c,#27ae60)"><div class="card-body d-flex justify-content-between align-items-center"><div><h6 class="mb-1 text-white-50">Transaksi</h6><h3 class="mb-0">{{ $stats['transaksi'] }}</h3></div><i class="mdi mdi-cash-multiple" style="font-size:38px;opacity:.4"></i></div></div></div>
    <div class="col-md-3 mb-3"><div class="card text-white" style="background:linear-gradient(135deg,#2d2d2d,#555)"><div class="card-body d-flex justify-content-between align-items-center"><div><h6 class="mb-1 text-white-50">Absensi Pegawai</h6><h3 class="mb-0">{{ $stats['absensi'] }}</h3></div><i class="mdi mdi-clock-check" style="font-size:38px;opacity:.4"></i></div></div></div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('backend.aktivitas.index') }}" method="GET" class="row mb-3">
            <div class="col-md-4">
                <select name="module" class="form-control" onchange="this.form.submit()">
                    <option value="">Semua Modul</option>
                    @foreach ($modules as $m)
                        <option value="{{ $m }}" {{ request('module') == $m ? 'selected' : '' }}>{{ ucfirst($m) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="action" class="form-control" onchange="this.form.submit()">
                    <option value="">Semua Aksi</option>
                    @foreach (['login','logout','create','update','delete','view','export'] as $a)
                        <option value="{{ $a }}" {{ request('action') == $a ? 'selected' : '' }}>{{ ucfirst($a) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><a href="{{ route('backend.aktivitas.index') }}" class="btn btn-secondary btn-block">Reset</a></div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="thead-light">
                    <tr><th width="60">Aksi</th><th>Deskripsi</th><th>Modul</th><th>Oleh</th><th>IP</th><th>Waktu</th></tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        @php $ic = $icons[$log->action_type] ?? ['mdi-information', '#666']; @endphp
                        <tr>
                            <td class="text-center"><i class="mdi {{ $ic[0] }}" style="font-size:22px;color:{{ $ic[1] }}"></i></td>
                            <td>{{ $log->description }}</td>
                            <td><span class="badge badge-light text-uppercase">{{ $log->module }}</span></td>
                            <td>
                                @if ($log->user)
                                    {{ $log->user->name }}
                                @elseif (!empty($log->properties['customer_nama']))
                                    {{ $log->properties['customer_nama'] }} <span class="badge badge-light">Customer</span>
                                @elseif (!empty($log->properties['customer_id']))
                                    <span class="text-muted">Customer #{{ $log->properties['customer_id'] }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td><small class="text-muted">{{ $log->ip_address }}</small></td>
                            <td><small>{{ $log->created_at->diffForHumans() }}</small><br><small class="text-muted">{{ $log->created_at->format('d/m/Y H:i') }}</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5"><i class="mdi mdi-pulse" style="font-size:48px;color:#ccc"></i><p class="mt-2 text-muted">Belum ada aktivitas tercatat</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
