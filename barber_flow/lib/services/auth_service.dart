import 'package:google_sign_in/google_sign_in.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'api_service.dart';
import '../config/app_config.dart';

/// Auth customer. Mode ditentukan terpusat oleh [AppConfig.useBackend].
/// Offline: login/register disimulasikan via SharedPreferences.
/// Online: memakai endpoint /login & /register (Sanctum token).
class AuthService {
  static const bool _offlineMode = !AppConfig.useBackend;

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

  static Future<void> logout() async {
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
