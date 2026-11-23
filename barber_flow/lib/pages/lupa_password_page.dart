import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../services/auth_service.dart';
import '../theme.dart';

/// Lupa password: pengguna hanya mengisi email. Permintaan dikirim ke admin; setelah
/// admin menyetujui, aplikasi ini menampilkan password sementara (sekali saja) yang
/// wajib diganti setelah login. Halaman mengembalikan email lewat Navigator.pop
/// supaya halaman login bisa mengisinya otomatis.
class LupaPasswordPage extends StatefulWidget {
  final String initialEmail;
  const LupaPasswordPage({super.key, this.initialEmail = ''});

  @override
  State<LupaPasswordPage> createState() => _LupaPasswordPageState();
}

class _LupaPasswordPageState extends State<LupaPasswordPage> {
  static const _interval = Duration(seconds: 4);

  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _emailCtrl;

  bool _loading = false;
  bool _menunggu = false; // sudah mengajukan, menunggu admin
  String? _passwordSementara;
  String? _info; // mis. ditolak / kedaluwarsa
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _emailCtrl = TextEditingController(text: widget.initialEmail);
    _lanjutkanBilaAda();
  }

  @override
  void dispose() {
    _timer?.cancel();
    _emailCtrl.dispose();
    super.dispose();
  }

  /// Bila sebelumnya sudah mengajukan dari perangkat ini, lanjutkan memantau.
  Future<void> _lanjutkanBilaAda() async {
    final email = await AuthService.pendingResetEmail();
    if (email == null || !mounted) return;
    setState(() {
      _emailCtrl.text = email;
      _menunggu = true;
    });
    _mulaiPantau();
  }

  void _mulaiPantau() {
    _timer?.cancel();
    _periksa();
    _timer = Timer.periodic(_interval, (_) => _periksa());
  }

  Future<void> _periksa() async {
    final hasil = await AuthService.checkPasswordReset();
    if (!mounted) return;
    switch (hasil.status) {
      case 'approved':
        _timer?.cancel();
        setState(() {
          _menunggu = false;
          _passwordSementara = hasil.tempPassword;
        });
        break;
      case 'rejected':
        _selesai('Permintaan ditolak admin. Hubungi admin bila Anda merasa ini keliru.');
        break;
      case 'expired':
        _selesai('Permintaan kedaluwarsa. Silakan ajukan lagi.');
        break;
      case 'delivered':
        _selesai('Password sementara sudah pernah ditampilkan. Ajukan reset lagi bila hilang.');
        break;
      case 'none':
        _timer?.cancel();
        setState(() => _menunggu = false);
        break;
      default:
        break; // pending: terus menunggu
    }
  }

  void _selesai(String pesan) {
    _timer?.cancel();
    setState(() {
      _menunggu = false;
      _info = pesan;
    });
  }

  Future<void> _ajukan() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() {
      _loading = true;
      _info = null;
    });
    final err = await AuthService.requestPasswordReset(_emailCtrl.text.trim());
    if (!mounted) return;
    setState(() => _loading = false);
    if (err != null) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(err), backgroundColor: Colors.red));
      return;
    }
    setState(() => _menunggu = true);
    _mulaiPantau();
  }

  Future<void> _batalkan() async {
    _timer?.cancel();
    await AuthService.clearPendingReset();
    if (!mounted) return;
    setState(() => _menunggu = false);
  }

  void _keLogin() => Navigator.pop(context, _emailCtrl.text.trim());

  Widget _kartuPassword() {
    return Card(
      color: Colors.green.shade50,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(children: [
          const Icon(Icons.check_circle, color: Colors.green, size: 44),
          const SizedBox(height: 8),
          const Text('Password Anda sudah direset oleh admin',
              textAlign: TextAlign.center, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          const SizedBox(height: 12),
          const Text('Password sementara Anda:'),
          const SizedBox(height: 6),
          SelectableText(
            _passwordSementara ?? '',
            style: const TextStyle(fontSize: 26, fontWeight: FontWeight.bold, letterSpacing: 2, fontFamily: 'monospace'),
          ),
          const SizedBox(height: 4),
          TextButton.icon(
            onPressed: () {
              Clipboard.setData(ClipboardData(text: _passwordSementara ?? ''));
              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Password disalin.')));
            },
            icon: const Icon(Icons.copy, size: 18),
            label: const Text('Salin'),
          ),
          const SizedBox(height: 8),
          const Text(
            'Catat password ini; hanya ditampilkan sekali. Gunakan untuk login, lalu Anda akan diminta membuat '
            'password baru. Password sementara berlaku 24 jam.',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 12, color: Colors.black54),
          ),
        ]),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Lupa Password')),
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.all(24),
          children: [
            const Icon(Icons.lock_reset, size: 64, color: AppColors.primary),
            const SizedBox(height: 12),
            if (_passwordSementara != null) ...[
              _kartuPassword(),
              const SizedBox(height: 16),
              ElevatedButton(onPressed: _keLogin, child: const Text('LOGIN SEKARANG')),
            ] else if (_menunggu) ...[
              const Center(child: CircularProgressIndicator(color: AppColors.primary)),
              const SizedBox(height: 16),
              const Text(
                'Permintaan terkirim. Menunggu persetujuan admin...',
                textAlign: TextAlign.center,
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              const Text(
                'Admin akan memverifikasi permintaan Anda. Biarkan halaman ini terbuka, atau tutup dan buka lagi '
                'nanti: password sementara tetap akan muncul di aplikasi ini setelah disetujui.',
                textAlign: TextAlign.center,
                style: TextStyle(color: Colors.black54),
              ),
              const SizedBox(height: 16),
              OutlinedButton(onPressed: _batalkan, child: const Text('BATALKAN PERMINTAAN')),
            ] else ...[
              const Text(
                'Masukkan email akun Anda. Permintaan akan dikirim ke admin untuk disetujui.',
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 20),
              Form(
                key: _formKey,
                child: TextFormField(
                  controller: _emailCtrl,
                  keyboardType: TextInputType.emailAddress,
                  decoration: const InputDecoration(
                    labelText: 'Email',
                    prefixIcon: Icon(Icons.email),
                    border: OutlineInputBorder(),
                  ),
                  validator: (v) {
                    if (v == null || v.trim().isEmpty) return 'Email wajib diisi';
                    if (!v.contains('@')) return 'Email tidak valid';
                    return null;
                  },
                ),
              ),
              if (_info != null) ...[
                const SizedBox(height: 12),
                Text(_info!, style: const TextStyle(color: Colors.red)),
              ],
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: _loading ? null : _ajukan,
                child: _loading
                    ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2))
                    : const Text('AJUKAN RESET PASSWORD'),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
