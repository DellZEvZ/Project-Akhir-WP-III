import 'package:flutter/material.dart';
import '../theme.dart';
import '../services/auth_service.dart';
import 'welcome_page.dart';
import 'riwayat_page.dart';
import 'ubah_password_page.dart';

/// Manajemen akun: lihat & ubah profil, lalu logout.
class AkunPage extends StatefulWidget {
  const AkunPage({super.key});

  @override
  State<AkunPage> createState() => _AkunPageState();
}

class _AkunPageState extends State<AkunPage> {
  final _namaCtrl = TextEditingController();
  final _hpCtrl = TextEditingController();
  final _alamatCtrl = TextEditingController();
  String _email = '';
  bool _loading = true;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final data = await AuthService.me();
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (data != null) {
        _namaCtrl.text = data['nama']?.toString() ?? '';
        _hpCtrl.text = data['no_hp']?.toString() ?? '';
        _alamatCtrl.text = data['alamat']?.toString() ?? '';
        _email = data['email']?.toString() ?? '';
      }
    });
  }

  Future<void> _save() async {
    setState(() => _saving = true);
    final err = await AuthService.updateProfile(
      nama: _namaCtrl.text.trim(),
      noHp: _hpCtrl.text.trim(),
      alamat: _alamatCtrl.text.trim(),
    );
    if (!mounted) return;
    setState(() => _saving = false);
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(err ?? 'Profil berhasil diperbarui.'),
      backgroundColor: err == null ? AppColors.dark : Colors.red,
    ));
  }

  Future<void> _logout() async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Keluar'),
        content: const Text('Yakin ingin keluar dari akun?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('BATAL')),
          TextButton(onPressed: () => Navigator.pop(context, true), child: const Text('KELUAR')),
        ],
      ),
    );
    if (ok != true) return;
    await AuthService.logout();
    if (!mounted) return;
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(builder: (_) => const WelcomePage()),
      (route) => false,
    );
  }

  Future<void> _hapusAkun() async {
    final passCtrl = TextEditingController();
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Hapus akun?'),
        content: Column(mainAxisSize: MainAxisSize.min, children: [
          const Text('Data pribadimu akan dihapus permanen. Riwayat pesanan tetap tersimpan di sistem tanpa identitasmu.'),
          const SizedBox(height: 12),
          TextField(
            controller: passCtrl,
            obscureText: true,
            decoration: const InputDecoration(
              labelText: 'Password',
              helperText: 'Kosongkan bila masuk dengan Google',
            ),
          ),
        ]),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('BATAL')),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('HAPUS AKUN', style: TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );
    if (ok != true) return;

    final err = await AuthService.deleteAccount(password: passCtrl.text);
    if (!mounted) return;
    if (err != null) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(err), backgroundColor: Colors.red));
      return;
    }
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(builder: (_) => const WelcomePage()),
      (route) => false,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Akun Saya')),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : ListView(
              padding: const EdgeInsets.all(20),
              children: [
                Center(
                  child: Column(children: [
                    const CircleAvatar(radius: 40, backgroundColor: AppColors.dark, child: Icon(Icons.person, size: 44, color: AppColors.primary)),
                    const SizedBox(height: 10),
                    Text(_namaCtrl.text.isEmpty ? 'Pelanggan' : _namaCtrl.text,
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
                    Text(_email, style: const TextStyle(color: Colors.grey)),
                  ]),
                ),
                const SizedBox(height: 20),
                Card(
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  child: ListTile(
                    leading: const Icon(Icons.receipt_long, color: AppColors.primary),
                    title: const Text('Riwayat Pesanan'),
                    subtitle: const Text('Lihat booking, status & struk'),
                    trailing: const Icon(Icons.chevron_right),
                    onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const RiwayatPage())),
                  ),
                ),
                const SizedBox(height: 8),
                Card(
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  child: ListTile(
                    leading: const Icon(Icons.lock_outline, color: AppColors.primary),
                    title: const Text('Ubah Password'),
                    subtitle: const Text('Ganti password akun Anda'),
                    trailing: const Icon(Icons.chevron_right),
                    onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const UbahPasswordPage())),
                  ),
                ),
                const SizedBox(height: 16),
                const Text('Edit Profil', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                const SizedBox(height: 12),
                TextField(controller: _namaCtrl, decoration: const InputDecoration(labelText: 'Nama', border: OutlineInputBorder())),
                const SizedBox(height: 12),
                TextField(controller: _hpCtrl, keyboardType: TextInputType.phone, decoration: const InputDecoration(labelText: 'No. HP', border: OutlineInputBorder())),
                const SizedBox(height: 12),
                TextField(controller: _alamatCtrl, maxLines: 2, decoration: const InputDecoration(labelText: 'Alamat', border: OutlineInputBorder())),
                const SizedBox(height: 18),
                ElevatedButton.icon(
                  onPressed: _saving ? null : _save,
                  icon: _saving
                      ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2))
                      : const Icon(Icons.save),
                  label: Text(_saving ? 'MENYIMPAN...' : 'SIMPAN PERUBAHAN'),
                ),
                const SizedBox(height: 12),
                OutlinedButton.icon(
                  onPressed: _logout,
                  style: OutlinedButton.styleFrom(foregroundColor: Colors.red),
                  icon: const Icon(Icons.logout),
                  label: const Text('KELUAR'),
                ),
                const SizedBox(height: 8),
                TextButton.icon(
                  onPressed: _hapusAkun,
                  style: TextButton.styleFrom(foregroundColor: Colors.red),
                  icon: const Icon(Icons.delete_forever),
                  label: const Text('HAPUS AKUN'),
                ),
              ],
            ),
    );
  }
}
