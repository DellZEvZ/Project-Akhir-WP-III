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
<style>
    @keyframes logFlash { from { background:#fff3cd; } to { background:transparent; } }
    .log-new { animation: logFlash 3s ease-out; }
    .live-dot { display:inline-block; width:9px; height:9px; border-radius:50%; margin-right:6px; background:#bbb; }
    .live-on .live-dot { background:#27ae60; box-shadow:0 0 0 0 rgba(39,174,96,.6); animation:livePulse 1.8s infinite; }
    .live-err .live-dot { background:#c0392b; }
    @keyframes livePulse { 70% { box-shadow:0 0 0 8px rgba(39,174,96,0); } 100% { box-shadow:0 0 0 0 rgba(39,174,96,0); } }
</style>

<div class="row">
    <div class="col-md-3 mb-3"><div class="card text-white" style="background:linear-gradient(135deg,#800020,#a0283c)"><div class="card-body d-flex justify-content-between align-items-center"><div><h6 class="mb-1 text-white-50">Aktivitas Hari Ini</h6><h3 class="mb-0" id="stat-hari_ini">{{ $stats['hari_ini'] }}</h3></div><i class="mdi mdi-pulse" style="font-size:38px;opacity:.4"></i></div></div></div>
    <div class="col-md-3 mb-3"><div class="card text-white" style="background:linear-gradient(135deg,#1f6f8b,#2e9bbd)"><div class="card-body d-flex justify-content-between align-items-center"><div><h6 class="mb-1 text-white-50">Aktivitas Pelanggan</h6><h3 class="mb-0" id="stat-pelanggan">{{ $stats['pelanggan'] }}</h3></div><i class="mdi mdi-account-group" style="font-size:38px;opacity:.4"></i></div></div></div>
    <div class="col-md-3 mb-3"><div class="card text-white" style="background:linear-gradient(135deg,#1a6b3c,#27ae60)"><div class="card-body d-flex justify-content-between align-items-center"><div><h6 class="mb-1 text-white-50">Transaksi</h6><h3 class="mb-0" id="stat-transaksi">{{ $stats['transaksi'] }}</h3></div><i class="mdi mdi-cash-multiple" style="font-size:38px;opacity:.4"></i></div></div></div>
    <div class="col-md-3 mb-3"><div class="card text-white" style="background:linear-gradient(135deg,#2d2d2d,#555)"><div class="card-body d-flex justify-content-between align-items-center"><div><h6 class="mb-1 text-white-50">Absensi Pegawai</h6><h3 class="mb-0" id="stat-absensi">{{ $stats['absensi'] }}</h3></div><i class="mdi mdi-clock-check" style="font-size:38px;opacity:.4"></i></div></div></div>
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
            <div class="col-md-3 text-right">
                <button type="button" id="liveToggle" class="btn btn-outline-secondary btn-block" title="Perbarui otomatis tiap beberapa detik">
                    <span class="live-dot"></span><span id="liveLabel">Live</span>
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="thead-light">
                    <tr><th width="60">Aksi</th><th>Deskripsi</th><th>Modul</th><th>Oleh</th><th>IP</th><th>Waktu</th></tr>
                </thead>
                <tbody id="logBody">
                    @forelse ($logs as $log)
                        @include('backend.v_activity._row', ['log' => $log])
                    @empty
                        <tr id="emptyRow"><td colspan="6" class="text-center py-5"><i class="mdi mdi-pulse" style="font-size:48px;color:#ccc"></i><p class="mt-2 text-muted">Belum ada aktivitas tercatat</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $logs->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var FEED_URL = @json(url('backend/aktivitas/feed'));
    var INTERVAL = 5000, RETRY = 15000, MAX_ROWS = 25;
    var latestId = {{ (int) $latestId }};
    var params = new URLSearchParams(window.location.search);
    var onFirstPage = !params.get('page') || params.get('page') === '1';

    var body = document.getElementById('logBody');
    var btn = document.getElementById('liveToggle');
    var label = document.getElementById('liveLabel');
    var timer = null, running = false;

    function store(k, v) { try { if (v === undefined) return localStorage.getItem(k); localStorage.setItem(k, v); } catch (e) {} return null; }
    var enabled = store('aktivitas_live') !== 'off';

    function setState(state, text) {
        btn.classList.remove('live-on', 'live-err');
        if (state === 'on') btn.classList.add('live-on');
        if (state === 'err') btn.classList.add('live-err');
        label.textContent = text;
    }

    // ---- waktu relatif (Indonesia) ----
    function ago(ts) {
        var s = Math.max(0, Math.round((Date.now() - new Date(ts).getTime()) / 1000));
        if (s < 45) return 'baru saja';
        var m = Math.round(s / 60); if (m < 60) return m + ' menit lalu';
        var h = Math.round(m / 60); if (h < 22) return h + ' jam lalu';
        var d = Math.round(h / 24); return d <= 1 ? 'kemarin' : d + ' hari lalu';
    }
    function refreshAgo() {
        document.querySelectorAll('.js-ago').forEach(function (el) { el.textContent = ago(el.dataset.ts); });
    }
    refreshAgo(); setInterval(refreshAgo, 30000);

    // ---- polling ----
    function schedule(ms) { clearTimeout(timer); if (enabled && onFirstPage) timer = setTimeout(poll, ms); }

    async function poll() {
        if (!enabled || !onFirstPage) return;
        if (document.hidden) { setState('on', 'Live (jeda)'); return; } // dilanjutkan saat tab aktif lagi
        if (running) return;
        running = true;
        try {
            var q = new URLSearchParams({ after: latestId });
            ['module', 'action'].forEach(function (k) { if (params.get(k)) q.set(k, params.get(k)); });
            var res = await fetch(FEED_URL + '?' + q.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            if (res.status === 401 || res.status === 419 || res.redirected) {
                setState('err', 'Sesi berakhir, muat ulang'); running = false; return;
            }
            if (!res.ok) throw new Error('HTTP ' + res.status);
            var data = await res.json();

            if (data.count > 0) {
                var empty = document.getElementById('emptyRow'); if (empty) empty.remove();
                var tpl = document.createElement('template'); tpl.innerHTML = data.html.trim();
                var rows = Array.prototype.slice.call(tpl.content.querySelectorAll('tr'));
                var first = body.firstChild;
                rows.forEach(function (tr) {
                    if (body.querySelector('tr[data-id="' + tr.dataset.id + '"]')) return; // hindari duplikat
                    tr.classList.add('log-new');
                    body.insertBefore(tr, first);
                });
                while (body.querySelectorAll('tr[data-id]').length > MAX_ROWS) { body.lastElementChild.remove(); }
                if (data.stats) Object.keys(data.stats).forEach(function (k) {
                    var el = document.getElementById('stat-' + k); if (el) el.textContent = data.stats[k];
                });
                refreshAgo();
            }
            latestId = Math.max(latestId, data.latest_id || 0);
            setState('on', 'Live');
            running = false; schedule(INTERVAL);
        } catch (e) {
            running = false; setState('err', 'Terputus, mencoba lagi'); schedule(RETRY);
        }
    }

    function apply() {
        if (!onFirstPage) { btn.disabled = true; setState('off', 'Live hanya di halaman 1'); return; }
        if (enabled) { setState('on', 'Live'); schedule(500); }
        else { clearTimeout(timer); setState('off', 'Live mati'); }
    }
    btn.addEventListener('click', function () {
        enabled = !enabled; store('aktivitas_live', enabled ? 'on' : 'off'); apply();
    });
    document.addEventListener('visibilitychange', function () { if (!document.hidden && enabled) { schedule(200); } });
    apply();
})();
</script>
@endpush
