/// Konfigurasi aplikasi.
///
/// Pilih [baseUrl] sesuai cara kamu menjalankan app:
///
/// ─────────────────────────────────────────────────────────────────
/// MODE 1 — HP FISIK via USB (direkomendasikan, paling stabil)
///   Jalankan di CMD/terminal laptop: adb reverse tcp:8000 tcp:8000
///   lalu set:
///     baseUrl = 'http://localhost:8000/api'
///
/// MODE 2 — ANDROID EMULATOR (bawaan Android Studio)
///   set:
///     baseUrl = 'http://10.0.2.2:8000/api'
///
/// MODE 3 — HP FISIK via WiFi (laptop & HP satu jaringan WiFi)
///   Cek IP laptop: ipconfig → cari IPv4 di adapter WiFi
///   set:
///     baseUrl = 'http://192.168.X.X:8000/api'
///   PENTING: pastikan firewall Windows mengizinkan port 8000.
///
/// MODE 4 — Windows desktop (test di komputer yang sama)
///   set:
///     baseUrl = 'http://127.0.0.1:8000/api'
/// ─────────────────────────────────────────────────────────────────
class AppConfig {
  /// SAKLAR TERPUSAT online/offline untuk SELURUH aplikasi.
  ///
  ///   true  → semua data (katalog, auth, booking) diambil dari backend
  ///           Laravel lewat REST API. Ini "versi terhubung".
  ///   false → mode demo tanpa server: data statis lokal, auth & booking
  ///           disimulasikan (SharedPreferences / in-memory).
  ///
  /// Semua service (CatalogService, AuthService, BookingService) membaca
  /// saklar ini — jadi tidak mungkin lagi setengah online / setengah offline
  /// (penyebab utama 401 & data palsu pada versi sebelumnya).
  static const bool useBackend = true;

  // ↓ Ganti sesuai mode yang dipakai saat ini
  static const String baseUrl = 'https://barbershop.seretia.link/api'; // PRODUKSI
  // static const String baseUrl = 'http://localhost:8000/api'; // MODE 1 (USB)
  // static const String baseUrl = 'http://10.0.2.2:8000/api';   // MODE 2 (Emulator)
  // static const String baseUrl = 'http://192.168.1.X:8000/api'; // MODE 3 (WiFi) ← ganti IP
  // static const String baseUrl = 'http://127.0.0.1:8000/api';  // MODE 4 (Desktop)

  /// Client ID tipe "Web application" dari Google Cloud (sama dengan
  /// GOOGLE_CLIENT_ID di backend). Dipakai sebagai serverClientId agar Google
  /// Sign-In native menghasilkan ID token yang diterima backend.
  static const String googleServerClientId =
      '109216385554-sibhjnv8pepvnnfc8a9itek7399d734p.apps.googleusercontent.com';

  /// Timeout permintaan HTTP (detik).
  static const int timeoutSeconds = 15;
}
