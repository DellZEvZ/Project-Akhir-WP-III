@extends('backend.v_layouts.app')

@section('breadcrumb')
<h4 class="page-title">API Tester</h4>
<div class="ml-auto">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('backend.beranda') }}">Home</a></li>
            <li class="breadcrumb-item active">API Tester</li>
        </ol>
    </nav>
</div>
@endsection

@section('content')
<style>
    .at-method { display:inline-block; min-width:52px; text-align:center; font-size:11px; font-weight:700; padding:2px 6px; border-radius:4px; color:#fff; }
    .at-GET { background:#27ae60; } .at-POST { background:#2e9bbd; } .at-PUT { background:#c9821f; } .at-DELETE { background:#c0392b; }
    .at-group { font-size:12px; font-weight:700; text-transform:uppercase; color:#888; margin:14px 0 6px; }
    .at-item { display:block; width:100%; text-align:left; border:0; background:transparent; padding:6px 8px; border-radius:6px; cursor:pointer; }
    .at-item:hover, .at-item.active { background:#f1e6e9; }
    .at-item small { color:#777; display:block; margin-left:60px; }
    .at-pre { background:#1e1e1e; color:#d4d4d4; padding:12px; border-radius:6px; max-height:460px; overflow:auto; font-size:12.5px; white-space:pre-wrap; word-break:break-word; }
    .at-code { font-family:Consolas,Menlo,monospace; }
    .at-status { font-weight:700; padding:2px 10px; border-radius:4px; color:#fff; }
</style>

<div class="alert alert-warning py-2">
    <i class="mdi mdi-alert"></i>
    Permintaan dikirim langsung dari browser ke API produksi. Aksi <b>POST/PUT/DELETE mengubah data asli</b>
    (dan tercatat di Traffic &amp; Aktivitas). Gunakan akun customer uji.
</div>

<div class="row">
    {{-- Daftar endpoint --}}
    <div class="col-lg-3 mb-3">
        <div class="card"><div class="card-body" id="endpointList" style="max-height:78vh;overflow:auto"></div></div>
    </div>

    {{-- Form permintaan --}}
    <div class="col-lg-4 mb-3">
        <div class="card"><div class="card-body">
            <h5 class="mb-3">Permintaan</h5>
            <div class="d-flex align-items-center mb-2">
                <span id="reqMethod" class="at-method at-GET mr-2">GET</span>
                <code id="reqPath" class="at-code">—</code>
            </div>
            <p class="text-muted small" id="reqDesc">Pilih endpoint di sebelah kiri.</p>

            <div id="paramBox" class="mb-2" style="display:none"></div>

            <div class="form-group" id="queryBox" style="display:none">
                <label class="small mb-1">Query string</label>
                <input type="text" id="reqQuery" class="form-control form-control-sm at-code" placeholder="search=bandung">
            </div>

            <div class="form-group">
                <label class="small mb-1">Bearer token <span id="tokenNote" class="text-muted"></span></label>
                <input type="text" id="reqToken" class="form-control form-control-sm at-code" placeholder="1|abcdef..." autocomplete="off">
            </div>

            <div class="form-group" id="bodyBox" style="display:none">
                <label class="small mb-1">Body (JSON)</label>
                <textarea id="reqBody" rows="10" class="form-control at-code" style="font-size:12.5px" spellcheck="false"></textarea>
            </div>

            <button id="btnSend" class="btn btn-danger btn-block" disabled><i class="mdi mdi-send"></i> Kirim</button>
        </div></div>

        <div class="card mt-3"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Riwayat</h6>
                <button id="btnClearHist" class="btn btn-sm btn-outline-secondary">Bersihkan</button>
            </div>
            <div id="history" class="small"></div>
        </div></div>
    </div>

    {{-- Respons --}}
    <div class="col-lg-5 mb-3">
        <div class="card"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Respons</h5>
                <button id="btnCopy" class="btn btn-sm btn-outline-secondary">Salin</button>
            </div>
            <div class="mb-2">
                <span id="resStatus" class="at-status" style="background:#999">—</span>
                <span id="resMeta" class="text-muted small ml-2"></span>
            </div>
            <ul class="nav nav-tabs mb-2">
                <li class="nav-item"><a class="nav-link active" href="#" data-tab="body">Body</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-tab="headers">Header</a></li>
            </ul>
            <pre id="resBody" class="at-pre at-code">Belum ada respons.</pre>
            <pre id="resHeaders" class="at-pre at-code" style="display:none"></pre>
        </div></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // url() (bukan route()) agar aman; base API di domain yang sama.
    var API_BASE = @json(url('api'));
    var CATALOG = @json($catalog);

    var $ = function (id) { return document.getElementById(id); };
    var current = null;

    function store(get, key, val) {
        try { return get ? JSON.parse(localStorage.getItem(key) || 'null') : localStorage.setItem(key, JSON.stringify(val)); }
        catch (e) { return null; }
    }
    function sget(key) { try { return sessionStorage.getItem(key) || ''; } catch (e) { return ''; } }
    function sset(key, v) { try { sessionStorage.setItem(key, v); } catch (e) {} }

    // ---- daftar endpoint ----
    var list = $('endpointList');
    Object.keys(CATALOG).forEach(function (group) {
        var h = document.createElement('div');
        h.className = 'at-group'; h.textContent = group; list.appendChild(h);
        CATALOG[group].forEach(function (ep) {
            var b = document.createElement('button');
            b.type = 'button'; b.className = 'at-item';
            var m = document.createElement('span'); m.className = 'at-method at-' + ep.method; m.textContent = ep.method;
            var p = document.createElement('code'); p.className = 'at-code ml-2'; p.textContent = ep.path;
            var d = document.createElement('small'); d.textContent = ep.desc;
            b.appendChild(m); b.appendChild(p); b.appendChild(d);
            b.addEventListener('click', function () { pick(ep, b); });
            list.appendChild(b);
        });
    });

    function pick(ep, btn) {
        current = ep;
        document.querySelectorAll('.at-item').forEach(function (x) { x.classList.remove('active'); });
        if (btn) btn.classList.add('active');

        $('reqMethod').className = 'at-method at-' + ep.method; $('reqMethod').textContent = ep.method;
        $('reqPath').textContent = '/api' + ep.path;
        $('reqDesc').textContent = ep.desc + (ep.auth ? ' (butuh token)' : ' (publik)');
        $('tokenNote').textContent = ep.auth ? '— wajib' : '— tidak perlu';

        var box = $('paramBox'); box.innerHTML = '';
        var params = (ep.path.match(/\{(\w+)\}/g) || []).map(function (s) { return s.slice(1, -1); });
        box.style.display = params.length ? '' : 'none';
        params.forEach(function (name) {
            var g = document.createElement('div'); g.className = 'form-group mb-2';
            var l = document.createElement('label'); l.className = 'small mb-1'; l.textContent = 'Parameter {' + name + '}';
            var i = document.createElement('input'); i.type = 'text'; i.className = 'form-control form-control-sm at-code';
            i.dataset.param = name; i.placeholder = '1';
            g.appendChild(l); g.appendChild(i); box.appendChild(g);
        });

        $('queryBox').style.display = ep.query !== undefined ? '' : 'none';
        $('reqQuery').value = ep.query || '';

        var hasBody = ep.method === 'POST' || ep.method === 'PUT';
        $('bodyBox').style.display = hasBody ? '' : 'none';
        $('reqBody').value = ep.body ? JSON.stringify(ep.body, null, 2) : (hasBody ? '{}' : '');
        $('btnSend').disabled = false;
    }

    // ---- token ----
    $('reqToken').value = sget('apitester_token');
    $('reqToken').addEventListener('input', function () { sset('apitester_token', this.value.trim()); });

    // ---- kirim ----
    function fmtBytes(n) { return n < 1024 ? n + ' B' : (n / 1024).toFixed(1) + ' KB'; }
    function statusColor(s) { return s >= 500 ? '#7b1d1d' : s >= 400 ? '#c0392b' : s >= 300 ? '#c9821f' : s >= 200 ? '#27ae60' : '#999'; }

    $('btnSend').addEventListener('click', async function () {
        if (!current) return;
        var path = current.path, bad = false;
        document.querySelectorAll('#paramBox input').forEach(function (i) {
            if (!i.value.trim()) { bad = true; i.classList.add('is-invalid'); }
            else { i.classList.remove('is-invalid'); path = path.replace('{' + i.dataset.param + '}', encodeURIComponent(i.value.trim())); }
        });
        if (bad) return;

        var url = API_BASE + path;
        var q = $('reqQuery').value.trim().replace(/^\?/, '');
        if (current.query !== undefined && q) url += '?' + q;

        var headers = { 'Accept': 'application/json' };
        var token = $('reqToken').value.trim();
        if (token) headers['Authorization'] = 'Bearer ' + token;

        var opt = { method: current.method, headers: headers, credentials: 'omit' };
        var bodyText = '';
        if (current.method === 'POST' || current.method === 'PUT') {
            bodyText = $('reqBody').value.trim();
            if (bodyText) {
                try { JSON.parse(bodyText); } catch (e) { alert('Body bukan JSON yang valid: ' + e.message); return; }
                headers['Content-Type'] = 'application/json';
                opt.body = bodyText;
            }
        }

        var btn = $('btnSend'); btn.disabled = true; btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Mengirim...';
        var t0 = performance.now();
        try {
            var res = await fetch(url, opt);
            var text = await res.text();
            var ms = Math.round(performance.now() - t0);
            var pretty = text, json = null;
            try { json = JSON.parse(text); pretty = JSON.stringify(json, null, 2); } catch (e) {}

            var st = $('resStatus'); st.textContent = res.status + ' ' + (res.statusText || ''); st.style.background = statusColor(res.status);
            $('resMeta').textContent = ms + ' ms · ' + fmtBytes(new Blob([text]).size);
            $('resBody').textContent = pretty || '(kosong)';
            var hs = []; res.headers.forEach(function (v, k) { hs.push(k + ': ' + v); });
            $('resHeaders').textContent = hs.join('\n');

            // login berhasil -> isi token otomatis
            if (json && json.data && typeof json.data.token === 'string') {
                $('reqToken').value = json.data.token; sset('apitester_token', json.data.token);
            }
            if (current.path === '/logout' && res.ok) { $('reqToken').value = ''; sset('apitester_token', ''); }

            addHistory({ method: current.method, path: path + (current.query !== undefined && q ? '?' + q : ''), status: res.status, ms: ms, ep: current.method + ' ' + current.path, body: bodyText, time: new Date().toLocaleTimeString('id-ID') });
        } catch (e) {
            var st2 = $('resStatus'); st2.textContent = 'Gagal'; st2.style.background = '#7b1d1d';
            $('resMeta').textContent = '';
            $('resBody').textContent = 'Tidak dapat menghubungi server: ' + e.message;
        } finally {
            btn.disabled = false; btn.innerHTML = '<i class="mdi mdi-send"></i> Kirim';
        }
    });

    // ---- tab respons & salin ----
    document.querySelectorAll('[data-tab]').forEach(function (a) {
        a.addEventListener('click', function (ev) {
            ev.preventDefault();
            document.querySelectorAll('[data-tab]').forEach(function (x) { x.classList.remove('active'); });
            a.classList.add('active');
            var showBody = a.dataset.tab === 'body';
            $('resBody').style.display = showBody ? '' : 'none';
            $('resHeaders').style.display = showBody ? 'none' : '';
        });
    });
    $('btnCopy').addEventListener('click', function () {
        var t = $('resBody').textContent;
        if (navigator.clipboard) navigator.clipboard.writeText(t);
    });

    // ---- riwayat (tanpa token) ----
    var HKEY = 'apitester_history';
    function renderHistory() {
        var box = $('history'); box.innerHTML = '';
        var h = store(true, HKEY) || [];
        if (!h.length) { box.textContent = 'Belum ada.'; return; }
        h.forEach(function (x) {
            var row = document.createElement('button'); row.type = 'button'; row.className = 'at-item';
            var m = document.createElement('span'); m.className = 'at-method at-' + x.method; m.textContent = x.method;
            var p = document.createElement('code'); p.className = 'at-code ml-2'; p.textContent = x.path;
            var s = document.createElement('span'); s.className = 'ml-2'; s.style.color = statusColor(x.status); s.textContent = x.status;
            var d = document.createElement('small'); d.textContent = x.time + ' · ' + x.ms + ' ms';
            row.appendChild(m); row.appendChild(p); row.appendChild(s); row.appendChild(d);
            row.addEventListener('click', function () { replay(x); });
            box.appendChild(row);
        });
    }
    function addHistory(x) {
        var h = store(true, HKEY) || [];
        h.unshift(x); store(false, HKEY, h.slice(0, 15)); renderHistory();
    }
    function replay(x) {
        var found = null;
        Object.keys(CATALOG).forEach(function (g) { CATALOG[g].forEach(function (ep) { if (ep.method + ' ' + ep.path === x.ep) found = ep; }); });
        if (!found) return;
        pick(found, null);
        if (x.body) $('reqBody').value = x.body;
    }
    $('btnClearHist').addEventListener('click', function () { store(false, HKEY, []); renderHistory(); });
    renderHistory();
})();
</script>
@endpush
