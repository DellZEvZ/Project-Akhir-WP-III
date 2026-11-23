import 'package:google_sign_in/google_sign_in.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'api_service.dart';
import '../config/app_config.dart';

/// Auth customer. Mode ditentukan terpusat oleh [AppConfig.useBackend].
/// Offline: login/register disimulasikan via SharedPreferences.
/// Online: memakai endpoint /login & /register (Sanctum token).
class AuthService {
  static const bool _offlineMode = !AppConfig.useBackend;

  /// true bila akun baru direset admin dan masih memakai password sementara
  /// (diisi saat login; app wajib membuka layar ganti password).
  static bool mustChangePassword = false;

  static const _keyNama = 'offline_nama';
  static const _keyEmail = 'offline_email';
  static const _keyPassword = 'offline_password';
  static const _keyNoHp = 'offline_no_hp';
  static const _keyAlamat = 'offline_alamat';
  static const _keyLoggedIn = 'offline_logged_in';

  // ── OFFLINE HELPERS ──────────────────────────────────────────────

  static Future<String?> register(String nama, String email, String password) async {
    if (_offlineMode) {
      final prefs = await SharedPreferences.getInstance();
      // Cek email sudah terdaftar
      final existing = prefs.getString(_keyEmail);
      if (existing == email) return 'Email sudah terdaftar.';
      await prefs.setString(_keyNama, nama);
      await prefs.setString(_keyEmail, email);
      await prefs.setString(_keyPassword, password);
      await prefs.setBool(_keyLoggedIn, true);
      // Simpan token dummy supaya ApiService.getToken() tidak null
      await ApiService.saveToken('offline-token-demo');
      return null;
    }
    try {
      final res = await ApiService.post('/register', {
        'nama': nama,
        'email': email,
        'password': password,
      });
      await ApiService.saveToken(res['data']['token']);
      return null;
    } on ApiException catch (e) {
      return e.message;
    } catch (_) {
      return 'Tidak dapat terhubung ke server.';
    }
  }

  static Future<String?> login(String email, String password) async {
    if (_offlineMode) {
      final prefs = await SharedPreferences.getInstance();
      final savedEmail = prefs.getString(_keyEmail);
      final savedPass = prefs.getString(_keyPassword);

      // Akun demo default jika belum pernah register
      final validEmail = savedEmail ?? 'demo@barberflow.com';
      final validPass = savedPass ?? 'demo123';

      if (email == validEmail && password == validPass) {
        await prefs.setBool(_keyLoggedIn, true);
        if (savedEmail == null) {
          await prefs.setString(_keyNama, 'Demo Customer');
          await prefs.setString(_keyEmail, validEmail);
        }
        await ApiService.saveToken('offline-token-demo');
        return null;
      }
      return 'Email atau password salah.';
    }
    try {
      final res = await ApiService.post('/login', {
        'email': email,
        'password': password,
      });
      await ApiService.saveToken(res['data']['token']);
      mustChangePassword = res['data']['customer']?['must_change_password'] == true;
      return null;
    } on ApiException catch (e) {
      return e.message;
    } catch (_) {
      return 'Tidak dapat terhubung ke server.';
    }
  }

  // ── LUPA PASSWORD (persetujuan admin) ────────────────────────────

  static const _keyResetToken = 'reset_request_token';
  static const _keyResetEmail = 'reset_request_email';

  /// Ajukan permintaan reset ke admin (hanya butuh email). Token permintaan disimpan
  /// di perangkat ini: hanya perangkat ini yang bisa menerima password sementara
  /// setelah admin menyetujui. Mengembalikan null bila terkirim, atau pesan error.
  static Future<String?> requestPasswordReset(String email) async {
    if (_offlineMode) return 'Lupa password membutuhkan koneksi ke server.';
    try {
      final res = await ApiService.post('/password/forgot', {'email': email});
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_keyResetToken, res['data']['request_token'].toString());
      await prefs.setString(_keyResetEmail, email);
      return null;
    } on ApiException catch (e) {
      return e.message;
    } catch (_) {
      return 'Tidak dapat terhubung ke server.';
    }
  }

  /// Email permintaan yang sedang menunggu di perangkat ini (null bila tidak ada).
  static Future<String?> pendingResetEmail() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_keyResetToken) == null ? null : prefs.getString(_keyResetEmail);
  }

  static Future<void> clearPendingReset() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_keyResetToken);
    await prefs.remove(_keyResetEmail);
  }

  /// Tanyakan status permintaan. status: none | pending | approved | rejected | expired | delivered.
  /// Saat `approved`, [tempPassword] berisi password sementara (hanya diserahkan SEKALI).
  static Future<({String status, String? tempPassword, String? error})> checkPasswordReset() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString(_keyResetToken);
    if (token == null) return (status: 'none', tempPassword: null, error: null);
    try {
      final res = await ApiService.post('/password/status', {'request_token': token});
      final data = Map<String, dynamic>.from(res['data']);
      final status = data['status']?.toString() ?? 'pending';
      if (status != 'pending') await clearPendingReset(); // selesai: tak perlu dipantau lagi
      return (status: status, tempPassword: data['temp_password']?.toString(), error: null);
    } on ApiException catch (e) {
      return (status: 'pending', tempPassword: null, error: e.message);
    } catch (_) {
      return (status: 'pending', tempPassword: null, error: 'Tidak dapat terhubung ke server.');
    }
  }

  /// Ganti password (juga untuk mengganti password sementara dari admin).
  static Future<String?> changePassword({required String current, required String password}) async {
    if (_offlineMode) return 'Ganti password membutuhkan koneksi ke server.';
    try {
      await ApiService.post('/me/password', {
        'current_password': current,
        'password': password,
      }, auth: true);
      mustChangePassword = false;
      return null;
    } on ApiException catch (e) {
      return e.message;
    } catch (_) {
      return 'Tidak dapat terhubung ke server.';
    }
  }

  static bool _googleReady = false;

  /// Login dengan akun Google (Google Sign-In native, bukan browser/WebView).
  /// ID token dikirim ke backend, yang memverifikasinya lalu mengeluarkan
  /// token Sanctum. Mengembalikan null bila sukses, atau pesan error.
  static Future<String?> loginWithGoogle() async {
    if (_offlineMode) return 'Login Google membutuhkan koneksi ke server.';
    try {
      final signIn = GoogleSignIn.instance;
      if (!_googleReady) {
        await signIn.initialize(serverClientId: AppConfig.googleServerClientId);
        _googleReady = true;
      }
      final account = await signIn.authenticate();
      final idToken = account.authentication.idToken;
      if (idToken == null) return 'Google tidak mengembalikan token.';

      final res = await ApiService.post('/login/google', {'id_token': idToken});
      await ApiService.saveToken(res['data']['token']);
      mustChangePassword = res['data']['customer']?['must_change_password'] == true;
      return null;
    } on GoogleSignInException catch (e) {
      if (e.code == GoogleSignInExceptionCode.canceled) return 'Login Google dibatalkan.';
      return 'Login Google gagal (${e.code.name}).';
    } on ApiException catch (e) {
      return e.message;
    } catch (_) {
      return 'Login Google gagal.';
    }
  }

  /// Hapus akun (server menganonimkan data; riwayat order tetap ada).
  /// [password] wajib untuk akun berpassword, kosong untuk akun Google.
  /// Mengembalikan null bila sukses, atau pesan error.
  static Future<String?> deleteAccount({String? password}) async {
    if (_offlineMode) {
      final prefs = await SharedPreferences.getInstance();
      for (final k in [_keyNama, _keyEmail, _keyPassword, _keyNoHp, _keyAlamat]) {
        await prefs.remove(k);
      }
      await prefs.setBool(_keyLoggedIn, false);
      await ApiService.clearToken();
      return null;
    }
    try {
      await ApiService.delete('/me', auth: true,
          body: (password == null || password.isEmpty) ? null : {'password': password});
      await ApiService.clearToken();
      return null;
    } on ApiException catch (e) {
      return e.message;
    } catch (_) {
      return 'Tidak dapat terhubung ke server.';
    }
  }

  static Future<void> logout() async {
    mustChangePassword = false;
    if (_offlineMode) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setBool(_keyLoggedIn, false);
      await ApiService.clearToken();
      return;
    }
    try {
      await ApiService.post('/logout', {}, auth: true);
    } catch (_) {}
    await ApiService.clearToken();
  }

  static Future<Map<String, dynamic>?> me() async {
    if (_offlineMode) {
      final prefs = await SharedPreferences.getInstance();
      final loggedIn = prefs.getBool(_keyLoggedIn) ?? false;
      if (!loggedIn) return null;
      return {
        'id': 1,
        'nama': prefs.getString(_keyNama) ?? 'Demo Customer',
        'email': prefs.getString(_keyEmail) ?? 'demo@barberflow.com',
        'no_hp': prefs.getString(_keyNoHp) ?? '',
        'alamat': prefs.getString(_keyAlamat) ?? '',
      };
    }
    try {
      final res = await ApiService.get('/me', auth: true);
      return Map<String, dynamic>.from(res['data']);
    } catch (_) {
      return null;
    }
  }

  static Future<String?> updateProfile({
    required String nama,
    String? noHp,
    String? alamat,
  }) async {
    if (_offlineMode) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_keyNama, nama);
      if (noHp != null) await prefs.setString(_keyNoHp, noHp);
      if (alamat != null) await prefs.setString(_keyAlamat, alamat);
      return null;
    }
    try {
      await ApiService.post('/me', {
        'nama': nama,
        'no_hp': noHp ?? '',
        'alamat': alamat ?? '',
      }, auth: true);
      return null;
    } on ApiException catch (e) {
      return e.message;
    } catch (_) {
      return 'Tidak dapat terhubung ke server.';
    }
  }
}
