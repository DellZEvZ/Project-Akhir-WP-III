import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import '../theme.dart';
import 'main_page.dart';

/// Ganti password. Mode [wajib] = setelah login dengan password sementara dari admin:
/// tidak bisa dilewati (tombol kembali dimatikan) dan berlanjut ke halaman utama.
class UbahPasswordPage extends StatefulWidget {
  final bool wajib;
  const UbahPasswordPage({super.key, this.wajib = false});

  @override
  State<UbahPasswordPage> createState() => _UbahPasswordPageState();
}

class _UbahPasswordPageState extends State<UbahPasswordPage> {
  final _formKey = GlobalKey<FormState>();
  final _lamaCtrl = TextEditingController();
  final _baruCtrl = TextEditingController();
  final _ulangCtrl = TextEditingController();
  bool _loading = false;

  @override
  void dispose() {
    _lamaCtrl.dispose();
    _baruCtrl.dispose();
    _ulangCtrl.dispose();
    super.dispose();
  }

  Future<void> _simpan() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _loading = true);
    final err = await AuthService.changePassword(current: _lamaCtrl.text, password: _baruCtrl.text);
    if (!mounted) return;
    setState(() => _loading = false);
    if (err != null) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(err), backgroundColor: Colors.red));
      return;
    }
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Password berhasil diubah.'), backgroundColor: AppColors.dark),
    );
    if (widget.wajib) {
      Navigator.pushAndRemoveUntil(
        context,
        MaterialPageRoute(builder: (_) => const MainPage()),
        (route) => false,
      );
    } else {
      Navigator.pop(context);
    }
  }

  Widget _field(TextEditingController c, String label, {String? Function(String?)? validator}) => Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: TextFormField(
          controller: c,
          obscureText: true,
          decoration: InputDecoration(
            labelText: label,
            prefixIcon: const Icon(Icons.lock),
            border: const OutlineInputBorder(),
          ),
          validator: validator,
        ),
      );

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: !widget.wajib,
      child: Scaffold(
        appBar: AppBar(
          title: Text(widget.wajib ? 'Buat Password Baru' : 'Ubah Password'),
          automaticallyImplyLeading: !widget.wajib,
        ),
        body: SafeArea(
          child: ListView(
            padding: const EdgeInsets.all(24),
            children: [
              if (widget.wajib)
                Container(
                  padding: const EdgeInsets.all(12),
                  margin: const EdgeInsets.only(bottom: 20),
                  decoration: BoxDecoration(color: Colors.orange.shade50, borderRadius: BorderRadius.circular(8)),
                  child: const Text(
                    'Anda masuk dengan password sementara dari admin. Buat password baru sebelum melanjutkan.',
                  ),
                ),
              Form(
                key: _formKey,
                child: Column(children: [
                  _field(_lamaCtrl, widget.wajib ? 'Password sementara' : 'Password saat ini',
                      validator: (v) => (v == null || v.isEmpty) ? 'Wajib diisi' : null),
                  _field(_baruCtrl, 'Password baru',
                      validator: (v) => (v == null || v.length < 6) ? 'Password minimal 6 karakter' : null),
                  _field(_ulangCtrl, 'Ulangi password baru',
                      validator: (v) => v != _baruCtrl.text ? 'Password tidak sama' : null),
                  ElevatedButton(
                    onPressed: _loading ? null : _simpan,
                    child: _loading
                        ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2))
                        : const Text('SIMPAN PASSWORD'),
                  ),
                ]),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
