import 'api_service.dart';
import '../config/app_config.dart';

/// Booking. Mode ditentukan terpusat oleh [AppConfig.useBackend].
/// Offline: disimpan in-memory selama sesi. Online: endpoint /booking (Sanctum).
class BookingService {
  static const bool _offlineMode = !AppConfig.useBackend;

  // In-memory storage untuk demo
  static int _nextId = 1;
  static final List<Map<String, dynamic>> _orders = [];

  // ── OFFLINE HELPERS ──────────────────────────────────────────────

  static Future<List<Map<String, dynamic>>> fetchOrders() async {
    if (_offlineMode) {
      // Kembalikan salinan terbalik (terbaru di atas)
      return List<Map<String, dynamic>>.from(_orders.reversed);
    }
    final res = await ApiService.get('/booking', auth: true);
    final List list = res['data'];
    return list.map((e) => Map<String, dynamic>.from(e)).toList();
  }

  /// Ubah jadwal booking (tanggal, jam, catatan). Server menolak bila bentrok,
  /// sudah dibayar, atau bukan booking layanan.
  static Future<Map<String, dynamic>> updateBooking(
    int orderId, {
    required String tanggal,
    required String jam,
    String catatan = '',
  }) async {
    if (_offlineMode) {
      final idx = _orders.indexWhere((o) => o['id'] == orderId);
      if (idx == -1) throw ApiException('Booking tidak ditemukan.');
      _orders[idx] = {
        ..._orders[idx],
        'tanggal_booking': tanggal,
        'jam_booking': jam,
        'catatan': catatan,
      };
      return Map<String, dynamic>.from(_orders[idx]);
    }
    final res = await ApiService.put('/booking/$orderId', {
      'tanggal_booking': tanggal,
      'jam_booking': jam,
      'catatan': catatan,
    }, auth: true);
    return Map<String, dynamic>.from(res['data']);
  }

  /// Batalkan pesanan (status jadi "batal", data tetap tercatat di admin).
  static Future<void> cancelBooking(int orderId) async {
    if (_offlineMode) {
      final idx = _orders.indexWhere((o) => o['id'] == orderId);
      if (idx == -1) throw ApiException('Pesanan tidak ditemukan.');
      _orders[idx] = {..._orders[idx], 'status': 'batal', 'status_label': 'Dibatalkan'};
      return;
    }
    await ApiService.delete('/booking/$orderId', auth: true);
  }

  static Future<Map<String, dynamic>> createBooking({
    required List<int> layananIds,
    required int barberId,
    required String tanggal,
    required String jam,
    String catatan = '',
    // Data tambahan untuk ditampilkan di summary (offline only)
    String layananNama = '',
    int layananHarga = 0,
    int layananDurasi = 0,
    String barberNama = '',
  }) async {
    if (_offlineMode) {
      // Cek bentrok jadwal (simulasi)
      final bentrok = _orders.any((o) =>
          o['jenis'] == 'booking' &&
          o['barber_id'] == barberId &&
          o['tanggal_booking'] == tanggal &&
          o['jam_booking'] == jam &&
          (o['status'] == 'confirmed' || o['status'] == 'done'));

      if (bentrok) {
        throw ApiException('$barberNama sudah dibooking pada jam $jam tanggal $tanggal. Silakan pilih jam lain.');
      }

      final order = {
        'id': _nextId++,
        'jenis': 'booking',
        'status': 'confirmed',
        'status_label': 'Dikonfirmasi',
        'status_bayar': 'belum',
        'status_bayar_label': 'Belum Bayar',
        'metode_bayar': null,
        'kanal_bayar': null,
        'no_ref': null,
        'dibayar_pada': null,
        'tanggal_booking': tanggal,
        'jam_booking': jam,
        'barber_id': barberId,
        'barber': {'id': barberId, 'nama': barberNama},
        'catatan': catatan,
        'total_harga': layananHarga,
        'biaya_ongkir': 0,
        'total_akhir': layananHarga,
        'alamat_kirim': null,
        'kota_tujuan_label': null,
        'kurir': null,
        'layanan_ongkir': null,
        'estimasi_ongkir': null,
        'items': layananIds.map((_) => {
              'nama': layananNama,
              'tipe': 'layanan',
              'qty': 1,
              'harga': layananHarga,
            }).toList(),
      };
      _orders.add(order);
      return Map<String, dynamic>.from(order);
    }

    final res = await ApiService.post('/booking', {
      'layanan_ids': layananIds,
      'barber_id': barberId,
      'tanggal_booking': tanggal,
      'jam_booking': jam,
      'catatan': catatan,
    }, auth: true);
    return Map<String, dynamic>.from(res['data']);
  }

  static Future<Map<String, dynamic>> createProdukOrder({
    required List<Map<String, dynamic>> items,
    required String alamatKirim,
    required String kotaTujuanId,
    required String kotaTujuanLabel,
    required String kurir,
    required String layananOngkir,
    required num biayaOngkir,
    String? estimasiOngkir,
    required num totalBerat,
    String catatan = '',
  }) async {
    if (_offlineMode) {
      final totalItem = items.fold<int>(
          0, (sum, i) => sum + ((i['harga'] as int) * (i['qty'] as int)));
      final totalAkhir = totalItem + biayaOngkir.toInt();

      final order = {
        'id': _nextId++,
        'jenis': 'produk',
        'status': 'confirmed',
        'status_label': 'Dikonfirmasi',
        'status_bayar': 'belum',
        'status_bayar_label': 'Belum Bayar',
        'metode_bayar': null,
        'kanal_bayar': null,
        'no_ref': null,
        'dibayar_pada': null,
        'tanggal_booking': null,
        'jam_booking': null,
        'barber': null,
        'alamat_kirim': alamatKirim,
        'kota_tujuan_label': kotaTujuanLabel,
        'kurir': kurir.toUpperCase(),
        'layanan_ongkir': layananOngkir,
        'biaya_ongkir': biayaOngkir.toInt(),
        'estimasi_ongkir': estimasiOngkir,
        'total_harga': totalItem,
        'total_akhir': totalAkhir,
        'catatan': catatan,
        'items': items.map((i) => {
              'nama': i['nama'],
              'tipe': 'produk',
              'qty': i['qty'],
              'harga': i['harga'],
            }).toList(),
      };
      _orders.add(order);
      return Map<String, dynamic>.from(order);
    }

    final res = await ApiService.post('/booking/produk', {
      'items': items,
      'alamat_kirim': alamatKirim,
      'kota_tujuan_id': kotaTujuanId,
      'kota_tujuan_label': kotaTujuanLabel,
      'kurir': kurir,
      'layanan_ongkir': layananOngkir,
      'biaya_ongkir': biayaOngkir,
      'estimasi_ongkir': estimasiOngkir,
      'total_berat': totalBerat,
      'catatan': catatan,
    }, auth: true);
    return Map<String, dynamic>.from(res['data']);
  }

  static Future<Map<String, dynamic>> payBooking(
    int orderId,
    String metode, {
    String? kanal,
  }) async {
    if (_offlineMode) {
      final idx = _orders.indexWhere((o) => o['id'] == orderId);
      if (idx == -1) throw ApiException('Pesanan tidak ditemukan.');

      final noRef = metode == 'cash'
          ? null
          : 'BF-${DateTime.now().millisecondsSinceEpoch.toString().substring(6)}-DEMO';

      _orders[idx] = {
        ..._orders[idx],
        'metode_bayar': metode,
        'kanal_bayar': metode == 'cash' ? 'Bayar di Tempat' : (kanal ?? metode),
        'status_bayar': metode == 'cash' ? 'belum' : 'lunas',
        'status_bayar_label': metode == 'cash' ? 'Belum Bayar' : 'Lunas',
        'no_ref': noRef,
        'dibayar_pada': metode == 'cash' ? null : DateTime.now().toString(),
      };

      return {
        'id': orderId,
        'metode_bayar': metode,
        'kanal_bayar': _orders[idx]['kanal_bayar'],
        'no_ref': noRef,
        'total_harga': _orders[idx]['total_harga'],
        'biaya_ongkir': _orders[idx]['biaya_ongkir'],
        'total_akhir': _orders[idx]['total_akhir'],
        'status_bayar': _orders[idx]['status_bayar'],
        'status_bayar_label': _orders[idx]['status_bayar_label'],
      };
    }

    final res = await ApiService.post('/booking/$orderId/pay', {
      'metode_bayar': metode,
      'kanal_bayar': kanal ?? '',
    }, auth: true);
    return Map<String, dynamic>.from(res['data']);
  }
}
